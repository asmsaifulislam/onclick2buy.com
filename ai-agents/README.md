# AI Agents Configuration Guide

## Overview

This guide covers setting up AI chatbot agents using Rasa, Botpress, and Microsoft Bot Framework for your Laravel e-commerce application.

## Supported Platforms

| Platform | Type | Best For |
|----------|------|----------|
| **Rasa** | Open Source | Full control, custom NLU, on-premise |
| **Botpress** | Open Source | Visual builder, quick setup |
| **Bot Framework** | Microsoft | Enterprise, multi-channel |

## Quick Start

### Option 1: Rasa (Recommended)

```bash
# Start Rasa
docker compose -f ai-agents/docker-compose.rasa.yml up -d

# Train the model
docker compose -f ai-agents/docker-compose.rasa.yml run rasa-train

# Test the bot
curl -X POST http://localhost:5005/webhooks/rest/webhook \
  -H "Content-Type: application/json" \
  -d '{"sender": "test", "message": "hello"}'
```

### Option 2: Botpress

```bash
# Start Botpress
docker compose -f ai-agents/docker-compose.botpress.yml up -d

# Access Botpress Studio
open http://localhost:3100
```

### Option 3: Microsoft Bot Framework

1. Create a bot in [Bot Framework Portal](https://dev.botframework.com)
2. Get App ID and Password
3. Update `.env`:
   ```
   BOTFRAMEWORK_APP_ID=your-app-id
   BOTFRAMEWORK_APP_PASSWORD=your-app-password
   ```

## Configuration

### Environment Variables

```env
# Active AI Provider
AI_AGENT_PROVIDER=rasa

# Rasa
RASA_URL=http://localhost:5005
RASA_ACTION_URL=http://localhost:5055

# Botpress
BOTPRESS_URL=http://localhost:3100
BOTPRESS_BOT_ID=ecommerce-bot

# Microsoft Bot Framework
BOTFRAMEWORK_APP_ID=
BOTFRAMEWORK_APP_PASSWORD=
BOTFRAMEWORK_CHANNEL_ID=webchat

# Handoff to Human
AI_HANDOFF_ENABLED=true
```

## File Structure

```
ai-agents/
├── docker-compose.rasa.yml      # Rasa Docker setup
├── docker-compose.botpress.yml  # Botpress Docker setup
├── rasa/
│   └── app/
│       ├── domain.yml           # Rasa domain
│       ├── config.yml           # Rasa pipeline config
│       ├── data/
│       │   ├── nlu.yml          # Training data
│       │   └── stories.yml      # Conversation flows
│       └── actions/
│           └── actions.py       # Custom actions
├── botpress/
│   └── bot/
│       └── bot.config.json      # Botpress config
└── botframework/
    └── bot.json                 # Bot Framework config
```

## API Integration

### Send Message to AI Agent

```javascript
// Frontend JavaScript
const response = await fetch('/api/ai/chat', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify({
        message: 'Hello',
        session_id: 'user-session-123',
        user_id: 1,        // optional
        page: 'home',      // optional
        product_id: 123,   // optional
    }),
});

const data = await response.json();
console.log(data.replies);
```

### Response Format

```json
{
    "success": true,
    "provider": "rasa",
    "replies": [
        {
            "type": "text",
            "content": "Hello! How can I help you?"
        }
    ],
    "intent": "greet",
    "confidence": 0.98
}
```

## Admin Panel

Navigate to **Admin → AI Agents** to:
- Switch between providers
- Test connections
- Train Rasa models
- View status

## Features

### Supported Intents

| Intent | Description |
|--------|-------------|
| `greet` | Welcome message |
| `goodbye` | Farewell message |
| `product_inquiry` | Product search |
| `order_status` | Order tracking |
| `shipping_info` | Shipping details |
| `return_policy` | Return information |
| `payment_methods` | Payment options |
| `contact_support` | Support contact |
| `human_handoff` | Transfer to human |

### Custom Actions

- **action_product_search**: Search products via Laravel API
- **action_order_tracking**: Track orders via Laravel API
- **action_human_handoff**: Transfer to live agent

## Training Rasa

### Add New Training Data

Edit `ai-agents/rasa/app/data/nlu.yml`:

```yaml
- intent: new_intent
  examples: |
    - example 1
    - example 2
```

### Retrain Model

```bash
# Via Docker
docker compose -f ai-agents/docker-compose.rasa.yml run rasa-train

# Via Admin Panel
# Admin → AI Agents → Train Model
```

### Test Intents

```bash
curl -X POST http://localhost:5005/model/parse \
  -H "Content-Type: application/json" \
  -d '{"text": "I want to check my order"}'
```

## Handoff to Human Agent

The AI agent will automatically transfer to a human when users say:
- "human"
- "agent"
- "person"
- "support"
- "help"

### Manual Handoff

```javascript
// Trigger handoff
const response = await fetch('/api/ai/chat', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify({
        message: 'I need a human agent',
        session_id: 'user-session-123',
    }),
});
```

## Troubleshooting

### Rasa Not Responding

```bash
# Check Rasa status
docker compose -f ai-agents/docker-compose.rasa.yml logs rasa

# Restart Rasa
docker compose -f ai-agents/docker-compose.rasa.yml restart rasa

# Retrain model
docker compose -f ai-agents/docker-compose.rasa.yml run rasa-train
```

### Botpress Not Responding

```bash
# Check Botpress status
docker compose -f ai-agents/docker-compose.botpress.yml logs botpress

# Restart Botpress
docker compose -f ai-agents/docker-compose.botpress.yml restart botpress
```

### Bot Framework Authentication Failed

1. Verify App ID and Password in `.env`
2. Check [Bot Framework Portal](https://dev.botframework.com)
3. Ensure bot is published

## Production Deployment

### Security

- Use HTTPS for all endpoints
- Validate webhook signatures
- Rate limit API endpoints
- Store credentials securely

### Performance

- Use Redis for session storage
- Implement response caching
- Monitor API response times
- Set appropriate timeouts

### Scaling

- Use multiple Rasa workers
- Implement load balancing
- Monitor resource usage
- Scale based on traffic

## References

- [Rasa Documentation](https://rasa.com/docs/rasa/)
- [Botpress Documentation](https://botpress.com/docs)
- [Microsoft Bot Framework](https://docs.microsoft.com/en-us/azure/bot-service/)
