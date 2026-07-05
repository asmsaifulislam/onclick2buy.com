"""
Product Recommendation Engine using Surprise (scikit-surprise)
FastAPI service for collaborative filtering recommendations
"""

from fastapi import FastAPI, HTTPException, Query
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import List, Optional, Dict, Any
import pandas as pd
import numpy as np
from surprise import Dataset, Reader, SVD, KNNBasic, NMF
from surprise.model_selection import cross_validate
from surprise import accuracy
import joblib
import os
import json
from datetime import datetime
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(
    title="Product Recommendation Engine",
    description="AI-powered product recommendations using Surprise",
    version="1.0.0"
)

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Global variables
model = None
reader = Reader(rating_scale=(1, 5))
MODEL_PATH = "models/recommendation_model.pkl"
DATA_PATH = "data/ratings.csv"


class Rating(BaseModel):
    user_id: int
    product_id: int
    rating: float
    timestamp: Optional[int] = None


class RatingBatch(BaseModel):
    ratings: List[Rating]


class RecommendationRequest(BaseModel):
    user_id: int
    n_recommendations: int = 10
    category: Optional[str] = None
    exclude_purchased: bool = True


class RecommendationResponse(BaseModel):
    user_id: int
    recommendations: List[Dict[str, Any]]
    algorithm: str
    timestamp: str


class ModelInfo(BaseModel):
    algorithm: str
    rmse: float
    mae: float
    total_ratings: int
    total_users: int
    total_products: int
    last_trained: str


# Model training and management
class RecommendationEngine:
    def __init__(self):
        self.model = None
        self.ratings_df = None
        self.product_names = {}
        self.algorithm = "SVD"
        self.last_trained = None
        self.model_metrics = {}
        
    def load_data(self) -> bool:
        """Load ratings data from CSV"""
        try:
            if os.path.exists(DATA_PATH):
                self.ratings_df = pd.read_csv(DATA_PATH)
                logger.info(f"Loaded {len(self.ratings_df)} ratings")
                return True
            else:
                logger.warning("No ratings data found")
                return False
        except Exception as e:
            logger.error(f"Error loading data: {e}")
            return False
    
    def load_product_names(self, products: Dict[int, str]):
        """Load product names from Laravel"""
        self.product_names = products
    
    def train_model(self, algorithm: str = "SVD") -> Dict[str, Any]:
        """Train the recommendation model"""
        try:
            if self.ratings_df is None or len(self.ratings_df) < 10:
                return {"success": False, "message": "Not enough data to train"}
            
            self.algorithm = algorithm
            
            # Create Surprise dataset
            data = Dataset.load_from_df(
                self.ratings_df[['user_id', 'product_id', 'rating']],
                reader
            )
            
            # Select algorithm
            algo_map = {
                'SVD': SVD(),
                'KNN': KNNBasic(),
                'NMF': NMF()
            }
            
            self.model = algo_map.get(algorithm, SVD())
            
            # Cross-validate
            cv_results = cross_validate(self.model, data, measures=['RMSE', 'MAE'], cv=5, verbose=False)
            
            # Train on full dataset
            trainset = data.build_full_trainset()
            self.model.fit(trainset)
            
            # Save metrics
            self.model_metrics = {
                'algorithm': algorithm,
                'rmse': float(cv_results['test_rmse'].mean()),
                'mae': float(cv_results['test_mae'].mean()),
                'total_ratings': len(self.ratings_df),
                'total_users': self.ratings_df['user_id'].nunique(),
                'total_products': self.ratings_df['product_id'].nunique(),
                'last_trained': datetime.now().isoformat()
            }
            
            self.last_trained = datetime.now()
            
            # Save model
            self.save_model()
            
            logger.info(f"Model trained: {self.model_metrics}")
            return {"success": True, "metrics": self.model_metrics}
            
        except Exception as e:
            logger.error(f"Training error: {e}")
            return {"success": False, "message": str(e)}
    
    def predict(self, user_id: int, product_id: int) -> float:
        """Predict rating for a user-product pair"""
        try:
            prediction = self.model.predict(user_id, product_id)
            return prediction.est
        except Exception as e:
            logger.error(f"Prediction error: {e}")
            return 3.0
    
    def get_recommendations(self, user_id: int, n: int = 10, 
                           exclude_purchased: bool = True,
                           category: Optional[str] = None) -> List[Dict[str, Any]]:
        """Get top N recommendations for a user"""
        try:
            if self.ratings_df is None:
                return []
            
            # Get products user hasn't rated
            user_ratings = self.ratings_df[self.ratings_df['user_id'] == user_id]['product_id'].tolist()
            
            if exclude_purchased:
                all_products = self.ratings_df['product_id'].unique()
                candidate_products = [p for p in all_products if p not in user_ratings]
            else:
                candidate_products = self.ratings_df['product_id'].unique()
            
            # If no candidate products, use all products from the system
            if not candidate_products:
                candidate_products = list(range(1, 1000))  # Fallback
            
            # Predict ratings for candidates
            predictions = []
            for product_id in candidate_products[:1000]:  # Limit for performance
                pred_rating = self.predict(user_id, product_id)
                predictions.append({
                    'product_id': int(product_id),
                    'predicted_rating': float(pred_rating),
                    'product_name': self.product_names.get(product_id, f"Product {product_id}")
                })
            
            # Sort by predicted rating
            predictions.sort(key=lambda x: x['predicted_rating'], reverse=True)
            
            # Return top N
            return predictions[:n]
            
        except Exception as e:
            logger.error(f"Recommendation error: {e}")
            return []
    
    def get_similar_products(self, product_id: int, n: int = 10) -> List[Dict[str, Any]]:
        """Get similar products based on rating patterns"""
        try:
            if self.ratings_df is None:
                return []
            
            # Get users who rated this product
            product_users = self.ratings_df[
                self.ratings_df['product_id'] == product_id
            ]['user_id'].tolist()
            
            if not product_users:
                return []
            
            # Find products rated by same users
            similar_products = self.ratings_df[
                (self.ratings_df['user_id'].isin(product_users)) &
                (self.ratings_df['product_id'] != product_id)
            ]['product_id'].unique()
            
            # Calculate similarity scores
            scores = []
            for sim_product in similar_products:
                # Average rating by common users
                sim_product_ratings = self.ratings_df[
                    self.ratings_df['product_id'] == sim_product
                ]['rating'].mean()
                
                scores.append({
                    'product_id': int(sim_product),
                    'similarity_score': float(sim_product_ratings),
                    'product_name': self.product_names.get(sim_product, f"Product {sim_product}")
                })
            
            # Sort by similarity
            scores.sort(key=lambda x: x['similarity_score'], reverse=True)
            
            return scores[:n]
            
        except Exception as e:
            logger.error(f"Similar products error: {e}")
            return []
    
    def add_rating(self, user_id: int, product_id: int, rating: float):
        """Add a new rating to the dataset"""
        try:
            new_rating = pd.DataFrame({
                'user_id': [user_id],
                'product_id': [product_id],
                'rating': [rating],
                'timestamp': [int(datetime.now().timestamp())]
            })
            
            if self.ratings_df is None:
                self.ratings_df = new_rating
            else:
                # Update existing or add new
                mask = (self.ratings_df['user_id'] == user_id) & \
                       (self.ratings_df['product_id'] == product_id)
                
                if mask.any():
                    self.ratings_df.loc[mask, 'rating'] = rating
                else:
                    self.ratings_df = pd.concat([self.ratings_df, new_rating], ignore_index=True)
            
            # Save updated data
            self.ratings_df.to_csv(DATA_PATH, index=False)
            
        except Exception as e:
            logger.error(f"Add rating error: {e}")
    
    def save_model(self):
        """Save model to disk"""
        try:
            os.makedirs(os.path.dirname(MODEL_PATH), exist_ok=True)
            joblib.dump({
                'model': self.model,
                'metrics': self.model_metrics,
                'product_names': self.product_names
            }, MODEL_PATH)
            logger.info("Model saved")
        except Exception as e:
            logger.error(f"Save model error: {e}")
    
    def load_model(self) -> bool:
        """Load model from disk"""
        try:
            if os.path.exists(MODEL_PATH):
                data = joblib.load(MODEL_PATH)
                self.model = data['model']
                self.model_metrics = data['metrics']
                self.product_names = data.get('product_names', {})
                logger.info("Model loaded")
                return True
            return False
        except Exception as e:
            logger.error(f"Load model error: {e}")
            return False


# Initialize engine
engine = RecommendationEngine()


@app.on_event("startup")
async def startup_event():
    """Initialize on startup"""
    # Load existing model or data
    engine.load_model()
    engine.load_data()


@app.get("/")
async def root():
    """API health check"""
    return {
        "service": "Product Recommendation Engine",
        "status": "running",
        "algorithm": engine.algorithm,
        "model_loaded": engine.model is not None
    }


@app.get("/health")
async def health():
    """Health check endpoint"""
    return {"status": "healthy", "timestamp": datetime.now().isoformat()}


@app.post("/recommendations")
async def get_recommendations(request: RecommendationRequest):
    """Get personalized recommendations for a user"""
    try:
        recommendations = engine.get_recommendations(
            user_id=request.user_id,
            n=request.n_recommendations,
            exclude_purchased=request.exclude_purchased,
            category=request.category
        )
        
        return RecommendationResponse(
            user_id=request.user_id,
            recommendations=recommendations,
            algorithm=engine.algorithm,
            timestamp=datetime.now().isoformat()
        )
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/recommendations/{user_id}")
async def get_recommendations_simple(
    user_id: int,
    n: int = Query(default=10, ge=1, le=50)
):
    """Simple endpoint to get recommendations"""
    try:
        recommendations = engine.get_recommendations(user_id=user_id, n=n)
        
        return {
            "user_id": user_id,
            "recommendations": recommendations,
            "count": len(recommendations)
        }
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/similar/{product_id}")
async def get_similar_products(
    product_id: int,
    n: int = Query(default=10, ge=1, le=50)
):
    """Get products similar to a given product"""
    try:
        similar = engine.get_similar_products(product_id=product_id, n=n)
        
        return {
            "product_id": product_id,
            "similar_products": similar,
            "count": len(similar)
        }
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/ratings")
async def add_rating(rating: Rating):
    """Add a new rating"""
    try:
        engine.add_rating(
            user_id=rating.user_id,
            product_id=rating.product_id,
            rating=rating.rating
        )
        
        return {"success": True, "message": "Rating added"}
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/ratings/batch")
async def add_ratings_batch(batch: RatingBatch):
    """Add multiple ratings at once"""
    try:
        for rating in batch.ratings:
            engine.add_rating(
                user_id=rating.user_id,
                product_id=rating.product_id,
                rating=rating.rating
            )
        
        return {"success": True, "message": f"{len(batch.ratings)} ratings added"}
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/train")
async def train_model(algorithm: str = Query(default="SVD")):
    """Train or retrain the model"""
    try:
        result = engine.train_model(algorithm=algorithm)
        return result
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/model/info")
async def get_model_info():
    """Get current model information"""
    return engine.model_metrics or {
        "algorithm": "None",
        "rmse": 0,
        "mae": 0,
        "total_ratings": 0,
        "total_users": 0,
        "total_products": 0,
        "last_trained": "Never"
    }


@app.post("/products/names")
async def update_product_names(products: Dict[int, str]):
    """Update product names mapping"""
    try:
        engine.load_product_names(products)
        return {"success": True, "message": "Product names updated"}
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/popular")
async def get_popular_products(
    n: int = Query(default=10, ge=1, le=50)
):
    """Get most popular products by average rating"""
    try:
        if engine.ratings_df is None or len(engine.ratings_df) == 0:
            return {"products": [], "count": 0}
        
        popular = engine.ratings_df.groupby('product_id').agg({
            'rating': ['mean', 'count']
        }).reset_index()
        
        popular.columns = ['product_id', 'avg_rating', 'rating_count']
        
        # Filter products with minimum ratings
        popular = popular[popular['rating_count'] >= 3]
        
        # Sort by average rating
        popular = popular.sort_values('avg_rating', ascending=False)
        
        result = []
        for _, row in popular.head(n).iterrows():
            result.append({
                'product_id': int(row['product_id']),
                'avg_rating': float(row['avg_rating']),
                'rating_count': int(row['rating_count']),
                'product_name': engine.product_names.get(
                    int(row['product_id']), 
                    f"Product {int(row['product_id'])}"
                )
            })
        
        return {"products": result, "count": len(result)}
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/trending")
async def get_trending_products(
    n: int = Query(default=10, ge=1, le=50),
    days: int = Query(default=30, ge=1, le=365)
):
    """Get trending products based on recent ratings"""
    try:
        if engine.ratings_df is None or len(engine.ratings_df) == 0:
            return {"products": [], "count": 0}
        
        # Filter recent ratings
        cutoff = int(datetime.now().timestamp()) - (days * 86400)
        recent_ratings = engine.ratings_df[
            engine.ratings_df['timestamp'] >= cutoff
        ] if 'timestamp' in engine.ratings_df.columns else engine.ratings_df
        
        trending = recent_ratings.groupby('product_id').agg({
            'rating': ['mean', 'count']
        }).reset_index()
        
        trending.columns = ['product_id', 'avg_rating', 'rating_count']
        
        # Weighted score (rating * log(count))
        trending['score'] = trending['avg_rating'] * np.log1p(trending['rating_count'])
        
        trending = trending.sort_values('score', ascending=False)
        
        result = []
        for _, row in trending.head(n).iterrows():
            result.append({
                'product_id': int(row['product_id']),
                'avg_rating': float(row['avg_rating']),
                'rating_count': int(row['rating_count']),
                'score': float(row['score']),
                'product_name': engine.product_names.get(
                    int(row['product_id']),
                    f"Product {int(row['product_id'])}"
                )
            })
        
        return {"products": result, "count": len(result)}
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8080)
