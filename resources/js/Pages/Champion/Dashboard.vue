<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <Head title="Champion Dashboard" />
        
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Champion Dashboard</h1>
                
                <!-- Desktop Table View -->
                <div class="hidden md:block bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Capital</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Profit</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">APY</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Age</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="champion in champions" :key="champion.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ champion.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                              :class="champion.archetype === 'farmer' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'">
                                            {{ champion.archetype }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-200">${{ champion.current_capital }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm" :class="champion.profit < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">${{ champion.profit }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-200">{{ champion.apy }}%</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-200">{{ champion.age }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <Link :href="route('champions.show', champion.id)" 
                                              class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            View Details
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">Total ({{ testData }})</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <!-- Type column - empty for footer -->
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">${{ totalCapital }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold" :class="totalProfit < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">${{ totalProfit }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold" :class="parseFloat(totalApy.replace(/,/g, '')) < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">{{ totalApy }}%</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <!-- Age column - empty for footer -->
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <!-- Actions column - empty for footer -->
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="md:hidden space-y-4">
                    <div v-for="champion in champions" :key="champion.id" class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ champion.name }}</h3>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                  :class="champion.archetype === 'farmer' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'">
                                {{ champion.archetype }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Capital</p>
                                <p class="text-gray-900 dark:text-gray-200">${{ champion.current_capital }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Profit</p>
                                <p :class="champion.profit < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">${{ champion.profit }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">APY</p>
                                <p class="text-gray-900 dark:text-gray-200">{{ champion.apy }}%</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Age</p>
                                <p class="text-gray-900 dark:text-gray-200">{{ champion.age }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Link :href="route('champions.show', champion.id)" 
                                  class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium">
                                View Details
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Link, Head } from '@inertiajs/inertia-vue3';

export default {
    components: {
        Link,
        Head
    },
    
    props: {
        champions: {
            type: Array,
            required: true
        }
    },
    
    computed: {
        testData() {
            return this.champions.length > 0 ? 'Data available' : 'No data';
        },
        
        totalCapital() {
            const total = this.champions.reduce((sum, champion) => {
                // Handle both string and number formats
                let capital = champion.current_capital;
                if (typeof capital === 'string') {
                    capital = parseFloat(capital.replace(/,/g, ''));
                }
                return sum + (isNaN(capital) ? 0 : capital);
            }, 0);
            return total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        
        totalProfit() {
            const total = this.champions.reduce((sum, champion) => {
                // Handle both string and number formats
                let profit = champion.profit;
                if (typeof profit === 'string') {
                    profit = parseFloat(profit.replace(/,/g, ''));
                }
                return sum + (isNaN(profit) ? 0 : profit);
            }, 0);
            return total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        
        totalApy() {
            const total = this.champions.reduce((sum, champion) => {
                let apy = champion.apy;
                if (typeof apy === 'string') {
                    apy = parseFloat(apy.replace(/,/g, ''));
                }
                return sum + (isNaN(apy) ? 0 : apy);
            }, 0);
            return total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    },
    
    mounted() {
        console.log('Champions data:', this.champions);
        console.log('First champion current_capital:', this.champions[0]?.current_capital);
        console.log('First champion profit:', this.champions[0]?.profit);
    }
}
</script> 