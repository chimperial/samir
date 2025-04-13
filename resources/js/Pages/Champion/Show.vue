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
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500 dark:text-gray-400">Archetype:</span>
                                <span>{{ champion.archetype }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500 dark:text-gray-400">Age:</span>
                                <span>{{ champion.age }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500 dark:text-gray-400">Current Capital:</span>
                                <span>${{ champion.current_capital }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500 dark:text-gray-400">APY:</span>
                                <span>{{ champion.apy }}%</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500 dark:text-gray-400">Grind:</span>
                                <span>{{ champion.grind }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500 dark:text-gray-400">Profit:</span>
                                <span>${{ champion.profit }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500 dark:text-gray-400">Income:</span>
                                <span>${{ champion.income }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500 dark:text-gray-400">Fee:</span>
                                <span>${{ champion.fee }}</span>
                            </div>
                            <div v-if="'lootcycle' === champion.archetype" class="flex items-center space-x-2">
                                <span class="text-gray-500 dark:text-gray-400">Entry:</span>
                                <span>${{ champion.entry }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders Card -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <div class="p-4 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-2">Recent Orders</h3>
                        <div v-if="orders && orders.length > 0" class="space-y-2">
                            <div v-for="order in orders.slice(0, 5)" :key="order.id" class="flex justify-between items-center py-1 border-b border-gray-200 dark:border-gray-700 last:border-0">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">#{{ order.id }}</span>
                                    <span class="font-medium">{{ order.type === 'BUY' ? 'Open Long' : order.type === 'SELL' ? 'Close Long' : order.type === 'SELL_SHORT' ? 'Open Short' : 'Close Short' }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ order.update_time }}</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="font-medium">{{ order.quantity }}</span>
                                    <span class="font-medium">@ ${{ order.avg_price }}</span>
                                    <span class="text-sm px-2 py-0.5 rounded" 
                                          :class="order.status === 'FILLED' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'">
                                        {{ order.status }}
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
