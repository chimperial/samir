# Samir

This is an application that interacts with Binance and TradingView to automate my trading strategy and capital management

## Account Management

The application includes an account management command that helps synchronize trading accounts and their activities. Here's how to use it:

### Account Command

The `account` command manages and syncs trading accounts, including active farmers and lootcycles. It handles synchronization of orders and trade collection.

```bash
# Sync all active accounts (default behavior)
php artisan account

# Explicitly sync all accounts
php artisan account --sync

# Sync only active farmers
php artisan account --farmers

# Sync only active lootcycles
php artisan account --lootcycles
```

The command performs the following actions:
- For farmers: Syncs account information and trading status
- For lootcycles: Syncs orders from the exchange and collects trades
