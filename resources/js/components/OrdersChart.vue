<template>
    <div >
        <div>
            <select v-model="selectedDateRange" @change="fetchData">
                <option value="all">All Time</option>
                <option value="last_week">Last Week</option>
                <option value="this_month">This Month</option>
                <option value="this_year">This Year</option>
                <option value="today">Today</option>
            </select>
        <span>
               ORDERS  {{total}}
            </span>
        </div>
        <LineChart :chartData="chartData" style="height: 350px"/>
    </div>
</template>

<script>
import { LineChart } from 'vue-chart-3';
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);
export default {
    name:'OrdersChart',
    components: { LineChart },
    data() {
        return {
            selectedDateRange: 'all',
            total: 0,
            chartData: {
                labels: [],
                datasets: [
                    {
                        label: 'Orders',
                        data: [],
                        backgroundColor: ['#123E6B']
                    },
                    {
                        label: 'Previous',
                        data: [],
                        backgroundColor: ['#555555']
                    },

                ],
            },
            chartOptions: {
                maintainAspectRatio: false,
            },
        };
    },
    methods: {
        async fetchData() {
            try {
                const response = await this.$axios.get('/api/ordersChart', {
                    params: {
                        selectedDateRange: this.selectedDateRange,
                    },
                });

                const currentData = response.data.data.current_data || [];
                const previousData = response.data.data.previous_data || [];
                this.total = response.data.data.total ?? 0;

                if (this.selectedDateRange === 'this_year') {
                    // When 'This Year' is selected, the response already has year, month, and total_amount
                    this.chartData.labels = currentData.map(item => {
                        const date = new Date(item.year, item.month - 1); // Subtract 1 from month as it is zero-based
                        return date.toLocaleString('default', { month: 'short' });
                    });
                    this.chartData.datasets[0].data = currentData.map(item => item.total_amount);

                    this.chartData.datasets[1].data = previousData.map(item => item.total_amount);

                } else if(this.selectedDateRange === 'this_month' || this.selectedDateRange === 'last_week') {
                    // For other date ranges, continue to format and populate the chart as before
                    const formattedCurrentData = this.formatData(currentData);
                    const formattedPreviousData = this.formatData(previousData);

                    this.chartData.labels = formattedCurrentData.map(item => item.date);
                    this.chartData.labels = formattedPreviousData.map(item => item.date);
                    this.chartData.datasets[0].data = formattedCurrentData.map(item => item.value);
                    this.chartData.datasets[1].data = formattedPreviousData.map(item => item.value);
                }

            } catch (e) {
                handleError(e, this.$toast);
            }
        },
        formatData  (data)  {
            return data.map(item => {
                const date = new Date(item.date);
                const month = date.toLocaleString('default', { month: 'short' });
                const day = date.getDate();
                return {
                    date: `${month}, ${day}`,
                    value: item.total_amount,
                };
            });
        }
    },
    async mounted() {
        await this.fetchData();
    },
};
</script>
