<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">System Activities</h1>
                    <Link :href="route('champions.dashboard')" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Dashboard
                    </Link>
                </div>
                
                <!-- Log Entries Card -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-base font-semibold mb-4">Recent System Logs</h3>
                        <div v-if="logEntries && logEntries.length > 0" class="space-y-2">
                            <div v-for="(entry, index) in logEntries" :key="index" 
                                class="flex flex-col py-2 px-4 rounded-lg" 
                                :class="{
                                    'bg-gray-50 dark:bg-gray-800': index % 2 === 0,
                                    'bg-white dark:bg-gray-700': index % 2 === 1
                                }">
                                <div class="flex flex-col sm:flex-row sm:items-start space-y-1 sm:space-y-0 sm:space-x-3">
                                    <span class="text-[10px] sm:text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">{{ entry.timestamp }}</span>
                                    <span class="text-sm text-gray-900 dark:text-gray-100 break-all">{{ entry.message }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-sm text-gray-500 dark:text-gray-400 py-2">
                            No log entries found
                        </div>
                        <div class="flex justify-center space-x-4 mt-4">
                            <button 
                                v-if="currentPage > 1"
                                @click="loadMore('back')" 
                                class="px-4 py-2 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 focus:outline-none border border-indigo-600 dark:border-indigo-400 rounded-md hover:bg-indigo-50 dark:hover:bg-indigo-900">
                                Back
                            </button>
                            <button 
                                v-if="hasMore"
                                @click="loadMore('next')" 
                                class="px-4 py-2 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 focus:outline-none border border-indigo-600 dark:border-indigo-400 rounded-md hover:bg-indigo-50 dark:hover:bg-indigo-900">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/inertia-vue3';
import { usePage } from '@inertiajs/inertia-vue3';

export default {
    components: {
        Link
    },
    props: {
        logEntries: {
            type: Array,
            default: () => [],
        },
        currentPage: {
            type: Number,
            default: 1,
        },
        hasMore: {
            type: Boolean,
            default: false,
        },
    },
    methods: {
        loadMore(direction) {
            const newPage = direction === 'next' ? this.currentPage + 1 : this.currentPage - 1;
            if ((direction === 'next' && this.hasMore) || (direction === 'back' && newPage > 0)) {
                this.$inertia.get(route('activities'), { page: newPage }, {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                    onSuccess: () => {
                        window.scrollTo(0, document.body.scrollHeight);
                    }
                });
            }
        }
    }
};
</script> 