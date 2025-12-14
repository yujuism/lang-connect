# LangConnect Documentation

This directory contains all technical documentation for the LangConnect project.

## 📚 Table of Contents

### Architecture & Best Practices

- **[CLEAN_ARCHITECTURE.md](CLEAN_ARCHITECTURE.md)** - Complete guide to the clean architecture implementation
- **[ARCHITECTURE_VISUAL.md](ARCHITECTURE_VISUAL.md)** - Visual diagrams and metrics for the architecture
- **[REFACTORING_SUMMARY.md](REFACTORING_SUMMARY.md)** - Summary of refactoring changes made

### Feature Documentation

#### WebSocket Real-Time Messaging
- **[WEBSOCKET_UPGRADE_COMPLETE.md](WEBSOCKET_UPGRADE_COMPLETE.md)** - Complete technical documentation
- **[WEBSOCKET_QUICK_START.md](WEBSOCKET_QUICK_START.md)** - Quick start guide for testing
- **[TEST_WEBSOCKET.md](TEST_WEBSOCKET.md)** - Step-by-step debugging guide
- **[SMART_NOTIFICATIONS.md](SMART_NOTIFICATIONS.md)** - Smart notification system documentation

#### Phase Completion Reports
- **[PHASE_1_COMPLETE.md](PHASE_1_COMPLETE.md)** - Phase 1 completion report
- **[PHASE_5A_COMPLETE.md](PHASE_5A_COMPLETE.md)** - Phase 5A completion report
- **[PHASE_5A_MESSAGING_COMPLETE.md](PHASE_5A_MESSAGING_COMPLETE.md)** - Messaging feature completion
- **[PHASE_5B_SESSION_ENHANCEMENT_COMPLETE.md](PHASE_5B_SESSION_ENHANCEMENT_COMPLETE.md)** - Session enhancement completion

### Project Planning

- **[LANGUAGE_EXCHANGE_PLAN.md](LANGUAGE_EXCHANGE_PLAN.md)** - Overall project plan
- **[COMPLETED_FEATURES.md](COMPLETED_FEATURES.md)** - List of completed features
- **[MATCHING_SYSTEM_UPDATE.md](MATCHING_SYSTEM_UPDATE.md)** - Matching system documentation

### Development Guides

- **[FACTORIES_GUIDE.md](FACTORIES_GUIDE.md)** - Guide for using Laravel factories and seeders

---

## 🏗️ Architecture Overview

LangConnect follows **Clean Architecture** principles with a service layer pattern:

```
HTTP Request → Controller → FormRequest → Service → Model → Database
```

- **Controllers**: Thin, handle HTTP only
- **FormRequests**: Validation layer
- **Services**: Business logic
- **Models**: Data access

See [CLEAN_ARCHITECTURE.md](CLEAN_ARCHITECTURE.md) for details.

---

## 🚀 Quick Links

- **Setup Guide**: See main [../README.md](../README.md)
- **WebSocket Setup**: [WEBSOCKET_QUICK_START.md](WEBSOCKET_QUICK_START.md)
- **Testing WebSocket**: [TEST_WEBSOCKET.md](TEST_WEBSOCKET.md)
- **Architecture Guide**: [CLEAN_ARCHITECTURE.md](CLEAN_ARCHITECTURE.md)

---

## 📖 Reading Order

If you're new to the project, read in this order:

1. [LANGUAGE_EXCHANGE_PLAN.md](LANGUAGE_EXCHANGE_PLAN.md) - Understand the project
2. [CLEAN_ARCHITECTURE.md](CLEAN_ARCHITECTURE.md) - Understand the code structure
3. [WEBSOCKET_QUICK_START.md](WEBSOCKET_QUICK_START.md) - Test real-time features
4. [FACTORIES_GUIDE.md](FACTORIES_GUIDE.md) - Learn about test data

---

**Last Updated**: December 13, 2025
