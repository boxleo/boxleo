<!-- EvaluationForm.vue -->
<template>
  <v-form ref="evaluationForm">
    <v-row>
      <v-col cols="12">
        <v-autocomplete
          v-model="evaluation.unit_id"
          :items="units"
          label="Unit"
          variant="outlined"
          item-title="name"
          item-value="id"
          clearable
          @update:modelValue="onUnitChange"
        />
      </v-col>
      <v-col cols="12">
        <v-autocomplete
          v-model="evaluation.department_id"
          :items="departments"
          label="Department"
          variant="outlined"
          item-title="name"
          item-value="id"
          clearable
          @update:modelValue="onDepartmentChange"
        />
      </v-col>
      <v-col cols="12" sm="6">
        <v-autocomplete
          v-model="evaluation.user_id"
          :items="team"
          item-title="fullname"
          item-value="id"
          label="Employee"
          clearable
          dense
        />
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          v-model="evaluation.attendance"
          label="Attendance"
          type="number"
          dense
          @input="calculateScores"
        />
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          v-model="evaluation.problems_solved"
          label="Problems Solved"
          type="number"
          dense
          @input="calculateScores"
        />
      </v-col>
      <v-col
        cols="12"
        sm="6"
        v-if="
          evaluation.user_id &&
          team.find((user) => user.id === evaluation.user_id)?.designation_id !== 1
        "
      >
        <v-text-field
          v-model="evaluation.reports_submitted"
          label="Reports Submitted"
          type="number"
          dense
          @input="calculateScores"
        />
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          v-model="evaluation.knowledge_of_work"
          label="Knowledge of Work"
          type="number"
          dense
          @input="calculateScores"
        />
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          v-model="evaluation.team_work"
          label="Team Work"
          type="number"
          dense
          @input="calculateScores"
        />
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          v-model="evaluation.reliability_visibility"
          label="Reliability & Visibility"
          type="number"
          dense
          @input="calculateScores"
        />
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          v-model="evaluation.productivity"
          label="Productivity"
          type="number"
          dense
          @input="calculateScores"
        />
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          v-model="evaluation.discipline"
          label="Discipline"
          type="number"
          dense
          @input="calculateScores"
        />
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          v-model="evaluation.quality_of_work"
          label="Quality of Work"
          type="number"
          dense
          @input="calculateScores"
        />
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          v-model="evaluation.communication"
          label="Communication"
          type="number"
          dense
          @input="calculateScores"
        />
      </v-col>
      <v-col
        cols="12"
        sm="6"
        v-if="
          evaluation.user_id &&
          team.find((user) => user.id === evaluation.user_id)?.designation_id === 1
        "
      >
        <v-text-field
          v-model="evaluation.leadership"
          label="Leadership"
          type="number"
          dense
          @input="calculateScores"
        />
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          v-model="evaluation.total_score"
          label="Total Score"
          type="number"
          dense
          disabled
        />
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          v-model="evaluation.percentage"
          label="Percentage"
          type="number"
          dense
          disabled
        />
      </v-col>
    </v-row>
  </v-form>
</template>

<script>
export default {
  props: {
    evaluation: {
      type: Object,
      required: true,
    },
    units: {
      type: Array,
      default: () => [],
    },
    departments: {
      type: Array,
      default: () => [],
    },
    team: {
      type: Array,
      default: () => [],
    },
  },
  emits: ['update:unit', 'update:department', 'update:scores'],
  methods: {
    onUnitChange() {
      this.$emit('update:unit');
    },
    onDepartmentChange() {
      this.$emit('update:department');
    },
    calculateScores() {
      const fields = [
        'attendance',
        'problems_solved',
        'reports_submitted',
        'knowledge_of_work',
        'team_work',
        'reliability_visibility',
        'productivity',
        'discipline',
        'quality_of_work',
        'communication',
        'leadership',
      ];

      const answered = fields.filter(
        (field) =>
          this.evaluation[field] !== null &&
          this.evaluation[field] !== undefined &&
          this.evaluation[field] !== ''
      );

      const total = answered.reduce((sum, field) => {
        return sum + (parseFloat(this.evaluation[field]) || 0);
      }, 0);

      const maxPossible = answered.length * 10;
      const percentage = maxPossible > 0 ? (total / maxPossible) * 100 : 0;

      this.evaluation.total_score = total;
      this.evaluation.percentage = Math.round(percentage * 100) / 100;

      this.$emit('update:scores');
    },
    resetForm() {
      this.$refs.evaluationForm.reset();
    },
  },
};
</script>