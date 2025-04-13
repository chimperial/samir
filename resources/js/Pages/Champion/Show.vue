<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">{{ champion.name }}</h1>
                
                <!-- Champion Basic Info Card -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Archetype:</span>
                                <span class="font-medium">{{ champion.archetype }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Age:</span>
                                <span class="font-medium">{{ champion.age }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Current Capital:</span>
                                <span class="font-medium">${{ champion.current_capital }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">APY:</span>
                                <span class="font-medium">{{ champion.apy }}%</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Grind:</span>
                                <span class="font-medium">{{ champion.grind }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Profit:</span>
                                <span class="font-medium">${{ champion.profit }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Income:</span>
                                <span class="font-medium">${{ champion.income }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Fee:</span>
                                <span class="font-medium">${{ champion.fee }}</span>
                            </div>
                            <div v-if="'lootcycle' === champion.archetype" class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Entry:</span>
                                <span class="font-medium">${{ champion.entry }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders Card -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <div class="p-4 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-2">Recent Orders</h3>
                        <div v-if="orders && orders.length > 0" class="space-y-2">
                            <div v-for="order in orders.slice(0, 5)" :key="order.id" class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-2 border-b border-gray-200 dark:border-gray-700 last:border-0">
                                <div class="flex flex-col sm:flex-row sm:items-center space-y-1 sm:space-y-0 sm:space-x-3">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">#{{ order.id }}</span>
                                        <span class="font-medium" :class="{
                                            'text-red-600 dark:text-red-400': order.type === 'Close Long' || order.type === 'Open Short',
                                            'text-green-600 dark:text-green-400': order.type === 'Open Long' || order.type === 'Close Short'
                                        }">{{ order.type }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="{
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': order.source === 'Human',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': order.source === 'Bot'
                                        }">
                                            <svg v-if="order.source === 'Human'" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <svg v-else class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                            </svg>
                                            {{ order.source }}
                                        </span>
                                    </div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ order.update_time }}</span>
                                </div>
                                <div class="flex items-center space-x-3 mt-2 sm:mt-0">
                                    <span class="font-medium">{{ order.quantity }}</span>
                                    <span class="font-medium">@ ${{ order.avg_price }}</span>
                                    <span class="text-sm px-2 py-0.5 rounded font-medium" :class="{
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': parseFloat(order.realized_pnl) < 0,
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': parseFloat(order.realized_pnl) > 0,
                                        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': parseFloat(order.realized_pnl) === 0
                                    }">
                                        {{ parseFloat(order.realized_pnl) > 0 ? '+' : '' }}${{ order.realized_pnl }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-gray-500 dark:text-gray-400 py-2">
                            No recent orders found
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        champion: {
            type: Object,
            required: true,
        },
        orders: {
            type: Array,
            default: () => [],
        },
    },
};
</script>
