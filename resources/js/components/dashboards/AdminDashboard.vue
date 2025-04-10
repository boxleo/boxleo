<template>
  <v-card class="gradient-card">
    <v-card-item>
      <v-card-title class="gradient-title">
        Dashboard
      </v-card-title>

      <v-card-subtitle class="gradient-subtitle">
        Welcome Back, {{ user.firstname }}!
      </v-card-subtitle>
    </v-card-item>
    <v-card-text>
      <v-row>
        <v-col cols="12" md="6" lg="3" v-for="(stat, index) in statistics" :key="index">
          <v-card class="animated-card gradient-card mb-4" :subtitle="stat.value" :title="stat.label" link>
            <template v-slot:prepend>
              <v-avatar color="purple">
                <v-icon :icon="stat.icon"></v-icon>
              </v-avatar>
            </template>
            <template v-slot:append>
              <v-avatar size="24">
                <v-icon color="red" icon="mdi-chart-bar"></v-icon>
              </v-avatar>
            </template>
            <v-card-text class="gradient-text">{{ stat.description }}</v-card-text>
          </v-card>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" md="6" lg="3" v-for="(status, index) in employeeStatus" :key="index">
          <v-card class="animated-card gradient-card mb-4" link>
            <v-card-text>
              <div class="d-flex justify-space-between align-center">
                <div>
                  <span class="d-block gradient-text">{{ status.label }}</span>
                </div>
              </div>
              <h3 class="mb-3 gradient-text">{{ status.value }}</h3>
              <v-progress-linear :value="status.percentage"
                :color="getProgressColor(status.percentage)" class="animated-progress"></v-progress-linear>
              <p class="mb-0 gradient-text">Percentage {{ status.percentage }} %</p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" lg="4" class="text-center">
          <v-card class="animated-card gradient-card mb-4">
            <AttendanceGraph />
          </v-card>
        </v-col>
        <v-col cols="12" lg="4" class="text-center">
          <v-card class="animated-card gradient-card mb-4">
            <DepartmentChart />
          </v-card>
        </v-col>
        <v-col cols="12" lg="4" class="text-center">
          <v-card class="animated-card gradient-card mb-4">
            <v-progress-linear v-if="loading" color="green" height="2" indeterminate rounded>
            </v-progress-linear>
            <AttendancePieChart />
          </v-card>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" lg="7">
          <v-card class="animated-card gradient-card shadow-sm mb-4">
            <v-card-title class="gradient-title text-left">Recent Leaves</v-card-title>
            <v-card-text>
              <v-row class="text-subtitle-1 font-weight-bold mb-2 gradient-text">
                <v-col cols="4">Employee</v-col>
                <v-col cols="4">Leave Type</v-col>
                <v-col cols="3">Status</v-col>
              </v-row>
              <v-divider></v-divider>
              <v-list class="elevation-0 transparent">
                <v-list-item v-for="(item, index) in recentLeaves" :key="item.id">
                  <v-row align="center">
                    <v-col cols="4" class="gradient-text">{{ item.user.firstname }} {{ item.user.lastname }}</v-col>
                    <v-col cols="4" class="gradient-text">{{ item.leave_type.name }}</v-col>
                    <v-col cols="3">
                      <v-chip :color="getStatusColor(item.status)" class="animated-chip">{{ item.status }}</v-chip>
                    </v-col>
                  </v-row>
                </v-list-item>
                <v-divider v-if="index < recentLeaves.length - 1"></v-divider>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" lg="5" class="text-center">
          <v-card class="animated-card gradient-card mb-4">
            <GenderChart />
          </v-card>
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>

  <!-- <style scoped>
  .gradient-card {
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    color: white;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s, box-shadow 0.3s;
  }

  .gradient-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
  }

  .gradient-title {
    font-size: 1.8rem;
    font-weight: bold;
    color: #ffffff;
  }

  .gradient-subtitle {
    font-size: 1.2rem;
    color: #e0e0e0;
  }

  .gradient-text {
    color: #ffffff;
  }

  .animated-card {
    animation: fadeIn 0.5s ease-in-out;
  }

  .animated-progress {
    animation: progressBar 1.5s ease-in-out infinite;
  }

  .animated-chip {
    animation: bounce 1s infinite;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes progressBar {
    0% {
      width: 0%;
    }
    100% {
      width: 100%;
    }
  }

  @keyframes bounce {
    0%, 100% {
      transform: translateY(0);
    }
    50% {
      transform: translateY(-5px);
    }
  }
  </style> -->
</template>

<script>
import DepartmentChart from '@/components/charts/DepartmentChart.vue';
import GenderChart from '@/components/charts/GenderChart.vue';
import AttendanceGraph from '@/components/charts/AttendanceGraph.vue';
import AttendancePieChart from '@/components/charts/AttendancePieChart.vue';

export default {
  name: 'Admin Dashboard',
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  components: {
    AttendanceGraph,
    DepartmentChart,
    GenderChart,
    AttendancePieChart
  },
  data() {
    return {
      base_url: '/',
      statistics: [],
      loading: false,
      employeeStatus: [],
      attendanceHeaders: [
        { title: '#', value: 'index' },
        { title: 'Date', value: 'clockin' },
        { title: 'Name', value: 'user' },
        { title: 'Status', value: 'status' },
      ],
      leaveHeaders: [
        { title: '#', value: 'index' },
        { title: 'Employee', value: 'user_id' },
        { title: 'Leave Type', value: 'leave_type_id' },
        { title: 'Status', value: 'status' },
      ],
      newEmployeeHeaders: [
        { title: '#', value: 'index' },
        { title: 'Employee', value: 'name' },
        { title: 'Department', value: 'department' },
        { title: 'Position', value: 'position' },
      ],
      recentAttendances: [],
      recentLeaves: [],
      newEmployees: [],
    };
  },
  mounted() {
    this.fetchStatistics();
    this.fetchLeaves();
    this.fetchUsers();
    this.fetchAttendaces();
  },
  methods: {
    getStatusColor(status) {
      switch (status) {
        case 'Approved':
          return 'success';
        case 'Pending':
          return 'warning';
        case 'Cancelled':
          return 'error';
        default:
          return 'primary';
      }
    },
    statusColor(status) {
      switch (status) {
        case 'In Time':
          return 'success';
        case 'Late':
          return 'danger';
        case 'Absent':
          return 'error';
        default:
          return 'primary';
      }
    },
    fetchAttendaces() {
      this.loading = true;
      const uri = `/api/v1/attendances`;
      axios.get(uri)
        .then(response => {
          this.recentAttendances = response.data.attendances.slice(0, 3);
        })
        .catch(error => {
          console.error('Error fetching attendances:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    fetchLeaves() {
      const uri = `/api/v1/leaves`;
      axios.get(uri)
        .then(response => {
          this.recentLeaves = response.data.leaves.slice(0, 3);
        })
        .catch(error => {
          console.error('Error fetching Leaves:', error);
        });
    },
    fetchUsers() {
      const uri = `/api/v1/users`;
      axios.get(uri)
        .then(response => {
          this.newEmployees = response.data.users.slice(-3);
        })
        .catch(error => {
          console.error('Error fetching users:', error);
        });
    },
    fetchStatistics() {
      axios.get('/api/v1/dashboard')
        .then(response => {
          this.statistics = response.data.statistics;
          this.employeeStatus = response.data.employeeStatus;
        })
        .catch(error => {
          console.error('Error fetching statistics:', error);
        });
    },
    getProgressColor(percentage) {
      if (percentage >= 80) {
        return 'success';
      } else if (percentage >= 50) {
        return 'warning';
      } else {
        return 'red';
      }
    }
  },
};
</script>

<style scoped>
.v-card {
  background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}

.v-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
}

.transparent {
  background-color: transparent !important;
}

.v-card-title {
  font-size: 1.5rem;
  font-weight: bold;
  color: #1e88e5;
}

.v-card-subtitle {
  font-size: 1.2rem;
  color: #546e7a;
}

.v-avatar {
  border: 2px solid #ffffff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.v-progress-linear {
  border-radius: 5px;
  height: 8px;
}

.v-chip {
  font-weight: bold;
  text-transform: uppercase;
}

.v-list-item {
  transition: background-color 0.3s;
}

.v-list-item:hover {
  background-color: rgba(30, 136, 229, 0.1);
}

.v-divider {
  background-color: #e0e0e0;
}
</style>
