<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ champion.name }}</h1>
                    <Link :href="route('champions.dashboard')" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Dashboard
                    </Link>
                </div>
                
                <!-- Champion Basic Info Card -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-base font-semibold mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Mobile-only collapsible section -->
                            <div class="md:hidden">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Net Earnings:</span>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium" :class="{
                                            'text-green-600 dark:text-green-400': netEarnings > 0,
                                            'text-red-600 dark:text-red-400': netEarnings < 0
                                        }">
                                            {{ netEarnings > 0 ? '+' : '' }}${{ netEarnings.toFixed(2) }}
                                        </span>
                                        <button @click="showDetails = true" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Collapsible section -->
                                <div v-if="showAdditionalInfo" class="mt-2 space-y-2">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Profile:</span>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-medium">{{ champion.archetype }}</span>
                                            <button @click="showProfileDetails = true" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Current Capital:</span>
                                        <span class="text-sm font-medium">${{ champion.current_capital }}</span>
                                    </div>
                                    <div v-if="'lootcycle' === champion.archetype" class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Entry:</span>
                                        <span class="text-sm font-medium">${{ champion.entry }}</span>
                                    </div>
                                </div>

                                <!-- Expand/Collapse button -->
                                <button @click="showAdditionalInfo = !showAdditionalInfo" class="w-full mt-2 flex items-center justify-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <span class="text-sm mr-1">{{ showAdditionalInfo ? 'Show Less' : 'Show More' }}</span>
                                    <svg class="w-4 h-4 transform transition-transform duration-200" :class="{ 'rotate-180': showAdditionalInfo }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Desktop view (unchanged) -->
                            <div class="hidden md:grid md:grid-cols-2 gap-4">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Profile:</span>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium">{{ champion.archetype }}</span>
                                        <button @click="showProfileDetails = true" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Current Capital:</span>
                                    <span class="text-sm font-medium">${{ champion.current_capital }}</span>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Net Earnings:</span>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium" :class="{
                                            'text-green-600 dark:text-green-400': netEarnings > 0,
                                            'text-red-600 dark:text-red-400': netEarnings < 0
                                        }">
                                            {{ netEarnings > 0 ? '+' : '' }}${{ netEarnings.toFixed(2) }}
                                        </span>
                                        <button @click="showDetails = true" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div v-if="'lootcycle' === champion.archetype" class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <span class="text-sm text-gray-500 dark:text-gray-400 mb-1 sm:mb-0">Entry:</span>
                                    <span class="text-sm font-medium">${{ champion.entry }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders Card -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <div class="p-4 text-gray-900 dark:text-gray-100">
                        <h3 class="text-base font-semibold mb-2">Recent Orders</h3>
                        <div v-if="orders && orders.length > 0" class="space-y-2">
                            <div v-for="(order, index) in orders.slice(0, 10)" :key="order.id" class="flex items-center justify-between py-2 px-4 rounded-lg" :class="{
                                'bg-gray-50 dark:bg-gray-800': index % 2 === 0,
                                'bg-white dark:bg-gray-700': index % 2 === 1
                            }">
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 hidden sm:inline">#{{ order.id }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded font-medium inline-block" :class="{
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': order.type === 'Close Long' || order.type === 'Open Short',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': order.type === 'Open Long' || order.type === 'Close Short'
                                    }">{{ order.type }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="{
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': order.source === 'Human',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': order.source === 'Bot'
                                    }">
                                        <svg v-if="order.source === 'Human'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                        </svg>
                                    </span>
                                    <span class="text-xs font-medium"><span class="hidden sm:inline">@ </span>${{ order.avg_price }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 hidden sm:inline">{{ order.update_time }}</span>
                                </div>
                                <div class="flex justify-end">
                                    <span v-if="parseFloat(order.realized_pnl) !== 0" class="text-xs px-2 py-0.5 rounded font-medium inline-block" :class="{
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': parseFloat(order.realized_pnl) < 0,
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': parseFloat(order.realized_pnl) > 0
                                    }">
                                        {{ parseFloat(order.realized_pnl) > 0 ? '+' : '' }}${{ order.realized_pnl }}
                                    </span>
                                    <span v-else class="text-xs px-2 py-0.5 rounded font-medium inline-block">&nbsp;</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-sm text-gray-500 dark:text-gray-400 py-2">
                            No recent orders found
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Details Modal -->
        <div v-if="showProfileDetails" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Profile Details</h3>
                    <button @click="showProfileDetails = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">Archetype:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ champion.archetype }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">Age:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ champion.age }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">Grind:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ champion.grind }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings Details Modal -->
        <div v-if="showDetails" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Earnings Breakdown</h3>
                    <button @click="showDetails = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">APY:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ champion.apy }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">Profit:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white" :class="{
                            'text-green-600 dark:text-green-400': parseFloat(champion.profit) > 0,
                            'text-red-600 dark:text-red-400': parseFloat(champion.profit) < 0
                        }">
                            {{ parseFloat(champion.profit) > 0 ? '+' : '' }}${{ champion.profit }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">Income:</span>
                        <span class="text-sm font-medium" :class="{
                            'text-green-600 dark:text-green-400': parseFloat(champion.income) > 0,
                            'text-red-600 dark:text-red-400': parseFloat(champion.income) < 0,
                            'text-gray-900 dark:text-white': parseFloat(champion.income) === 0
                        }">
                            {{ parseFloat(champion.income) > 0 ? '+' : parseFloat(champion.income) < 0 ? '-' : '' }}${{ Math.abs(parseFloat(champion.income)) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">Fee:</span>
                        <span class="text-sm font-medium text-red-600 dark:text-red-400">-${{ champion.fee }}</span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="flex justify-between">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Total:</span>
                            <span class="text-sm font-semibold" :class="{
                                'text-green-600 dark:text-green-400': netEarnings > 0,
                                'text-red-600 dark:text-red-400': netEarnings < 0
                            }">
                                {{ netEarnings > 0 ? '+' : '' }}${{ netEarnings.toFixed(2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/inertia-vue3';

export default {
    components: {
        Link
    },
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
    data() {
        return {
            showDetails: false,
            showProfileDetails: false,
            showAdditionalInfo: false,
        };
    },
    computed: {
        netEarnings() {
            const profit = parseFloat(this.champion.profit) || 0;
            const income = parseFloat(this.champion.income) || 0;
            const fee = parseFloat(this.champion.fee) || 0;
            return profit + income - fee;
        },
    },
};
</script>
