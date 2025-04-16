<template>
  <v-container fluid>
    <!-- Header Section -->
    <v-row>
      <v-col cols="12">
        <v-card class="mb-4 pa-4" elevation="2" rounded="lg">
          <div class="d-flex justify-space-between align-center">
            <h1 class="text-h5 font-weight-bold">Recruitment Dashboard</h1>
            <div>
              <v-menu>
                <template v-slot:activator="{ props }">
                  <v-btn v-bind="props" color="primary" variant="outlined" class="ml-2">
                    <v-icon start>mdi-filter</v-icon>
                    Filter
                  </v-btn>
                </template>
                <v-card min-width="300">
                  <v-list>
                    <v-list-item title="All Departments"></v-list-item>
                    <v-list-item title="Technical"></v-list-item>
                    <v-list-item title="Non-Technical"></v-list-item>
                  </v-list>
                </v-card>
              </v-menu>
              <v-btn color="primary" class="ml-2">
                <v-icon start>mdi-plus</v-icon>
                New Job
              </v-btn>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Key Metrics Section -->
    <v-row>
      <v-col cols="12" md="3">
        <v-card class="mb-4" elevation="2" rounded="lg" color="#FFF8EF">
          <v-card-text>
            <div class="text-overline text-blue-grey-darken-1">Technical Hiring</div>
            <div class="d-flex justify-space-between align-center">
              <div class="text-h3 font-weight-bold">18</div>
              <v-icon>mdi-arrow-right</v-icon>
            </div>
            <div class="text-subtitle-1">Average Days to Hire</div>
          </v-card-text>
        </v-card>
      </v-col>
      
      <v-col cols="12" md="3">
        <v-card class="mb-4" elevation="2" rounded="lg" color="#EFFFEF">
          <v-card-text>
            <div class="text-overline text-blue-grey-darken-1">Non-Technical Hiring</div>
            <div class="d-flex justify-space-between align-center">
              <div class="text-h3 font-weight-bold">23</div>
              <v-icon>mdi-arrow-right</v-icon>
            </div>
            <div class="text-subtitle-1">Average Days to Hire</div>
          </v-card-text>
        </v-card>
      </v-col>
      
      <v-col cols="12" md="3">
        <v-card class="mb-4" elevation="2" rounded="lg" color="#EFF8FF">
          <v-card-text>
            <div class="text-overline text-blue-grey-darken-1">Technical Hiring</div>
            <div class="d-flex justify-space-between align-center">
              <div class="text-h3 font-weight-bold">98%</div>
              <v-icon>mdi-arrow-right</v-icon>
            </div>
            <div class="text-subtitle-1">Offer Acceptance Rate</div>
          </v-card-text>
        </v-card>
      </v-col>
      
      <v-col cols="12" md="3">
        <v-card class="mb-4" elevation="2" rounded="lg" color="#FFF0F0">
          <v-card-text>
            <div class="text-overline text-blue-grey-darken-1">Non-Technical Hiring</div>
            <div class="d-flex justify-space-between align-center">
              <div class="text-h3 font-weight-bold">99%</div>
              <v-icon>mdi-arrow-right</v-icon>
            </div>
            <div class="text-subtitle-1">Offer Acceptance Rate</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Activity & Analysis Section -->
    <v-row>
      <v-col cols="12" md="8">
        <v-card elevation="2" rounded="lg" class="mb-4">
          <v-card-title class="d-flex justify-space-between align-center">
            <span>Hiring Performance</span>
            <v-btn-toggle v-model="timeRange" mandatory variant="outlined" color="primary" density="comfortable">
              <v-btn value="month">Month</v-btn>
              <v-btn value="quarter">Quarter</v-btn>
              <v-btn value="year">Year</v-btn>
            </v-btn-toggle>
          </v-card-title>
          <v-card-text>
            <v-chart class="chart" :option="chartOption" autoresize></v-chart>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card elevation="2" rounded="lg" class="mb-4">
          <v-card-title>Recruitment Costs YTD</v-card-title>
          <v-card-text>
            <v-chart class="chart" :option="costChartOption" autoresize></v-chart>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Top Sources & Countries -->
    <v-row>
      <v-col cols="12" md="6">
        <v-card elevation="2" rounded="lg" class="mb-4">
          <v-card-title class="d-flex justify-space-between align-center">
            <span>Top Hiring Sources Q2</span>
            <div>
              <v-chip color="error" size="small" class="mr-2">Engineering</v-chip>
              <v-chip color="primary" size="small">Marketing</v-chip>
            </div>
          </v-card-title>
          <v-card-text>
            <v-chart class="chart" :option="sourcesChartOption" autoresize></v-chart>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card elevation="2" rounded="lg" class="mb-4">
          <v-card-title class="d-flex justify-space-between align-center">
            <span>Top Countries</span>
            <v-btn variant="text" size="small">View All</v-btn>
          </v-card-title>
          <v-card-text class="px-2">
            <v-list>
              <v-list-item v-for="country in topCountries" :key="country.name">
                <template v-slot:prepend>
                  <v-avatar size="30">
                    <v-img :src="country.flag" alt="Country flag"></v-img>
                  </v-avatar>
                </template>
                <v-list-item-title>{{ country.name }}</v-list-item-title>
                <template v-slot:append>
                  <div class="d-flex align-center">
                    <span class="text-subtitle-1 font-weight-bold mr-2">${{ country.cost.toLocaleString() }}</span>
                    <v-icon :color="country.trend === 'up' ? 'success' : 'error'">
                      {{ country.trend === 'up' ? 'mdi-trending-up' : 'mdi-trending-down' }}
                    </v-icon>
                  </div>
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Applications & Job Recommendations -->
    <v-row>
      <v-col cols="12" md="7">
        <v-card elevation="2" rounded="lg">
          <v-card-title class="d-flex justify-space-between align-center">
            <span>Recent Applications</span>
            <v-btn variant="text" size="small">View All</v-btn>
          </v-card-title>
          <v-table>
            <thead>
              <tr>
                <th>Candidate</th>
                <th>Position</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="app in recentApplications" :key="app.id">
                <td>
                  <div class="d-flex align-center">
                    <v-avatar size="32" class="mr-3">
                      <v-img :src="app.avatar"></v-img>
                    </v-avatar>
                    <div>{{ app.name }}</div>
                  </div>
                </td>
                <td>{{ app.position }}</td>
                <td>
                  <v-chip :color="getStatusColor(app.status)" size="small">
                    {{ app.status }}
                  </v-chip>
                </td>
                <td>{{ app.date }}</td>
                <td>
                  <v-btn icon size="small" variant="text">
                    <v-icon>mdi-eye</v-icon>
                  </v-btn>
                  <v-btn icon size="small" variant="text">
                    <v-icon>mdi-dots-vertical</v-icon>
                  </v-btn>
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card>
      </v-col>

      <v-col cols="12" md="5">
        <v-card elevation="2" rounded="lg">
          <v-card-title>Recommended Jobs</v-card-title>
          <v-card-text class="pa-2">
            <v-list>
              <v-list-item v-for="job in recommendedJobs" :key="job.id">
                <template v-slot:prepend>
                  <v-avatar rounded="lg" color="blue" class="d-flex justify-center align-center">
                    <v-icon color="white">{{ job.icon }}</v-icon>
                  </v-avatar>
                </template>

                <v-list-item-title>{{ job.title }}</v-list-item-title>
                <v-list-item-subtitle>{{ job.company }}</v-list-item-subtitle>

                <template v-slot:append>
                  <v-btn color="primary" variant="text" size="small" class="mr-2">
                    <v-icon start>mdi-plus</v-icon>
                    Follow
                  </v-btn>
                </template>
              </v-list-item>
              <v-divider v-if="recommendedJobs.length > 1"></v-divider>
            </v-list>
          </v-card-text>
          <v-card-actions class="justify-center">
            <v-btn color="primary" variant="text">View More Jobs</v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { LineChart, BarChart } from 'echarts/charts';
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent
} from 'echarts/components';
import VChart from 'vue-echarts';

use([
  CanvasRenderer,
  LineChart,
  BarChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent
]);

export default {
  name: 'RecruitmentDashboard',
  components: {
    VChart
  },
  data() {
    return {
      timeRange: 'quarter',
      chartOption: {
        tooltip: {
          trigger: 'axis'
        },
        legend: {
          data: ['Applications', 'Interviews', 'Hires']
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true
        },
        xAxis: {
          type: 'category',
          boundaryGap: false,
          data: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug']
        },
        yAxis: {
          type: 'value'
        },
        series: [
          {
            name: 'Applications',
            type: 'line',
            smooth: true,
            data: [30, 40, 35, 50, 49, 60, 70, 65],
            itemStyle: { color: '#4CAF50' }
          },
          {
            name: 'Interviews',
            type: 'line',
            smooth: true,
            data: [15, 20, 25, 30, 35, 40, 60, 50],
            itemStyle: { color: '#2196F3' }
          },
          {
            name: 'Hires',
            type: 'line',
            smooth: true,
            data: [5, 8, 10, 12, 11, 15, 18, 14],
            itemStyle: { color: '#FFC107' }
          }
        ]
      },
      costChartOption: {
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'shadow'
          }
        },
        legend: {
          data: ['External', 'Internal']
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true
        },
        xAxis: {
          type: 'category',
          data: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug']
        },
        yAxis: {
          type: 'value',
          name: 'Cost ($)',
          axisLabel: {
            formatter: '${value}k'
          }
        },
        series: [
          {
            name: 'External',
            type: 'bar',
            stack: 'total',
            data: [10, 12, 21, 18, 16, 12, 15, 11],
            itemStyle: { color: '#2196F3' }
          },
          {
            name: 'Internal',
            type: 'bar',
            stack: 'total',
            data: [2, 3, 4, 3, 5, 4, 2, 1],
            itemStyle: { color: '#90CAF9' }
          }
        ]
      },
      sourcesChartOption: {
        tooltip: {
          trigger: 'axis',
          formatter: '{b}: {c}'
        },
        xAxis: {
          type: 'category',
          data: ['Direct', 'We Work', 'LinkedIn', 'Hired', 'Work In', 'Instagram', 'Referral']
        },
        yAxis: {
          type: 'value'
        },
        series: [
          {
            data: [60, 70, 42, 78, 65, 40, 50],
            type: 'line',
            smooth: true,
            areaStyle: {
              opacity: 0.3
            },
            lineStyle: {
              width: 3
            },
            itemStyle: {
              color: '#673AB7'
            },
            markPoint: {
              data: [
                { name: 'Engineering', coord: ['LinkedIn', 42], itemStyle: { color: '#F44336' } },
                { name: 'Marketing', coord: ['Hired', 78], itemStyle: { color: '#3F51B5' } }
              ],
              symbol: 'circle',
              symbolSize: 10
            }
          }
        ]
      },
      topCountries: [
        { name: 'Kenya', flag: '/api/placeholder/30/30', cost: 17965, trend: 'up' },
        { name: 'Tanzania', flag: '/api/placeholder/30/30', cost: 15844, trend: 'down' },
        { name: 'Uganda', flag: '/api/placeholder/30/30', cost: 19584, trend: 'up' },
        { name: 'Zambia', flag: '/api/placeholder/30/30', cost: 14509, trend: 'down' },
        { name: 'Ethiopia', flag: '/api/placeholder/30/30', cost: 18450, trend: 'up' }
      ],
      recentApplications: [
        { id: 1, name: 'Alice Chen', position: 'UI/UX Designer', status: 'Interview', date: '2025-04-10', avatar: '/api/placeholder/32/32' },
        { id: 2, name: 'Michael Brown', position: 'Software Engineer', status: 'Review', date: '2025-04-12', avatar: '/api/placeholder/32/32' },
        { id: 3, name: 'Sarah Johnson', position: 'Product Manager', status: 'Shortlisted', date: '2025-04-12', avatar: '/api/placeholder/32/32' },
        { id: 4, name: 'James Wilson', position: 'Data Analyst', status: 'Rejected', date: '2025-04-11', avatar: '/api/placeholder/32/32' },
        { id: 5, name: 'Emma Davis', position: 'Marketing Specialist', status: 'Hired', date: '2025-04-09', avatar: '/api/placeholder/32/32' }
      ],
      recommendedJobs: [
        { id: 1, title: 'UX Designer', company: 'Maximax Team', icon: 'mdi-palette-outline', salary: '$14,000 - $25,000', location: 'London, England', type: 'Full-Time' },
        { id: 2, title: 'Senior UX Designer', company: 'Insyte Studios', icon: 'mdi-monitor-dashboard', salary: '$21,000 - $25,000', location: 'Manchester, England', type: 'Full-Time' },
        { id: 3, title: 'Freelance UI Designer', company: 'Nazmata Team', icon: 'mdi-palette-swatch', salary: '$14,000 - $25,000', location: 'London, England', type: 'Freelance' }
      ]
    };
  },
  methods: {
    getStatusColor(status) {
      const colors = {
        'Interview': 'primary',
        'Review': 'info',
        'Shortlisted': 'success',
        'Rejected': 'error',
        'Hired': 'success'
      };
      return colors[status] || 'grey';
    }
  }
};
</script>

<style scoped>
.chart {
  height: 300px;
}
</style>