from typing import Any, Text, Dict, List
from rasa_sdk import Action, Tracker
from rasa_sdk.executor import CollectingDispatcher
from rasa_sdk.events import SlotSet, UserUtteranceReverted
import requests
import json

class ActionProductSearch(Action):
    def name(self) -> Text:
        return "action_product_search"

    def run(
        self,
        dispatcher: CollectingDispatcher,
        tracker: Tracker,
        domain: Dict[Text, Any],
    ) -> List[Dict[Text, Any]]:

        product_name = tracker.get_slot("product_name")

        if not product_name:
            dispatcher.utter_message(text="What product are you looking for?")
            return []

        # Call Laravel API to search products
        try:
            response = requests.get(
                f"http://localhost:8080/api/products?search={product_name}",
                timeout=5
            )

            if response.status_code == 200:
                data = response.json()
                products = data.get("data", [])

                if products:
                    message = f"I found {len(products)} product(s) matching '{product_name}':\n\n"
                    for product in products[:5]:
                        price = product.get("sale_price") or product.get("price")
                        message += f"• **{product['name']}** - ${price}\n"
                        message += f"  {product.get('description', '')[:100]}...\n\n"
                    message += "Would you like more details about any of these products?"
                else:
                    message = f"Sorry, I couldn't find any products matching '{product_name}'. Would you like to browse our categories?"
            else:
                message = "I'm having trouble searching for products right now. Please try again later."
        except Exception as e:
            message = "I'm having trouble connecting to our product database. Please try again later."

        dispatcher.utter_message(text=message)
        return [SlotSet("product_name", None)]


class ActionOrderTracking(Action):
    def name(self) -> Text:
        return "action_order_tracking"

    def run(
        self,
        dispatcher: CollectingDispatcher,
        tracker: Tracker,
        domain: Dict[Text, Any],
    ) -> List[Dict[Text, Any]]:

        order_id = tracker.get_slot("order_id")

        if not order_id:
            dispatcher.utter_message(text="Please provide your order ID (e.g., ORD-12345678).")
            return []

        # Call Laravel API to track order
        try:
            response = requests.get(
                f"http://localhost:8080/api/orders/{order_id}",
                timeout=5
            )

            if response.status_code == 200:
                order = response.json()
                status = order.get("status", "unknown")
                total = order.get("total", 0)

                status_messages = {
                    "pending": "Your order is being processed.",
                    "processing": "Your order is being prepared for shipment.",
                    "shipped": "Your order has been shipped!",
                    "delivered": "Your order has been delivered.",
                    "cancelled": "Your order has been cancelled.",
                }

                message = f"**Order {order_id}**\n\n"
                message += f"Status: {status.title()}\n"
                message += f"{status_messages.get(status, 'Status unknown.')}\n"
                message += f"Total: ${total}\n\n"
                message += "Is there anything else you'd like to know about your order?"
            elif response.status_code == 404:
                message = f"I couldn't find order {order_id}. Please check your order ID and try again."
            else:
                message = "I'm having trouble looking up your order. Please try again later."
        except Exception as e:
            message = "I'm having trouble connecting to our order system. Please try again later."

        dispatcher.utter_message(text=message)
        return [SlotSet("order_id", None)]


class ActionHumanHandoff(Action):
    def name(self) -> Text:
        return "action_human_handoff"

    def run(
        self,
        dispatcher: CollectingDispatcher,
        tracker: Tracker,
        domain: Dict[Text, Any],
    ) -> List[Dict[Text, Any]]:

        # Notify Laravel about human handoff
        try:
            requests.post(
                "http://localhost:8080/api/chat/handoff",
                json={
                    "session_id": tracker.sender_id,
                    "reason": "user_requested",
                },
                timeout=5
            )
        except Exception as e:
            pass

        dispatcher.utter_message(text="I'm connecting you with a human agent. Please hold on for a moment. A support agent will be with you shortly.")

        return []
