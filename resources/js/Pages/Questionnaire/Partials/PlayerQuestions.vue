<script setup>
import { ref, watch, reactive,computed } from 'vue';
import { useForm,usePage,router } from '@inertiajs/vue3';

// Import components
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { formatDate } from '@/utils';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import QuestionsLayout from '@/Layouts/QuestionsLayout.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';


// Props for game and questions
const props = defineProps({
    game: Object,
    user: Object,
    questions: Array,
});
const { props: pageProps } = usePage();
const error = ref();

// Create a reactive form object using useForm
const form = useForm({
    answers: {}, // Store answers keyed by question ID
});

const questionDateValues = reactive({});

const initialAnswers = {};
props.questions.forEach(question => {
        initialAnswers[question.id] = question.answer;
    });
    form.answers = initialAnswers;

// Populate form.answers based on the answers prop
if (props.answers && props.answers.length > 0) {
    const initialAnswers = {};
    props.answers.forEach(answer => {
        initialAnswers[answer.question_id] = answer.answer;
    });
    form.answers = initialAnswers; // Replace form.answers with a reactive object
}
/***********************
 *
 *  Date Dropdowns
 *
************************/
// Month names
const months = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
];

// Function to calculate days in a selected month and year
const getDaysInMonth = (month, year) => {
  return new Date(year, month, 0).getDate();
};

// Computed property for days in a month for each question
const daysInMonth = (questionId) => {
  const { selectedMonth, selectedYear } = questionDateValues[questionId];
  return Array.from({ length: getDaysInMonth(selectedMonth, selectedYear) }, (_, i) => i + 1);
};

// Array for years (2020 going back to 1920)
const years = Array.from({ length: 101 }, (_, i) => 2020 - i);

//populate date dropdowns with current date or previous answer
props.questions
  .filter((question) => question.question_type === 'date')
  .forEach((question) => {
    const existingValue = form.answers[question.id];

    if (existingValue) {
      // If form.answers[question.id] exists, parse the YYYY-MM-DD value
      const [year, month, day] = existingValue.split('-').map(Number);

      questionDateValues[question.id] = {
        selectedYear: year || 2020,                 // Use parsed year, fallback to 2020
        selectedMonth: month || new Date().getMonth() + 1, // Use parsed month, fallback to current
        selectedDay: day || new Date().getDate(),   // Use parsed day, fallback to current
      };
    } else {
      // Default initialization if no value exists
      questionDateValues[question.id] = {
        selectedYear: 2020,                      // Default year
        selectedMonth: new Date().getMonth() + 1, // Default to current month
        selectedDay: new Date().getDate(),       // Default to current day
      };
    }
  });

// Update date answer in form.
watch(
  () => questionDateValues,
  () => {
    Object.entries(questionDateValues).forEach(([questionId, { selectedMonth, selectedDay, selectedYear }]) => {
      form.answers[questionId] = `${selectedYear}-${String(selectedMonth).padStart(2, '0')}-${String(
        selectedDay
      ).padStart(2, '0')}`;
    });
  },
  { deep: true, immediate: true }
);


// Submit answers function
const submitAnswers = () => {
    form.post(route('questions.playerAnswers', { game: props.game.id, user: props.user.id }), {
        onSuccess: (response) => {
            if (pageProps.auth.user) {
                localStorage.setItem('flashMessage', 'Your answers were successfully updated.');
                router.visit(route('games.show', { game: props.game.id }), {
                    preserveState: false,
                    preserveScroll: true,
                });
            }
        },
        onError: (errors) => {
            error.value = errors.message;
        },
    });
};

const cancel = () => {
    router.visit(route('games.show', { game: props.game.id }))
}
const answeredCount = computed(() => props.questions.filter(q => q.answer !== null).length);

watch(
  () => form.answers,
  (newAnswers) => {
    props.questions.forEach((question) => {
      if (newAnswers[question.id] !== undefined) {
        question.answer = newAnswers[question.id]; // Update question.answer with latest answer
      }
    });
  },
  { deep: true }
);


</script>

<template>
        <QuestionsLayout title="Questions">
            <template #header>
                <ApplicationLogo class="flex justify-center block h-24 mx-auto w-auto mb-5" />
            </template>
                <div class="text-base mt-5 mb-5">
                    Welcome<span v-if="answeredCount>0"> back</span>, {{ user.first_name }} {{ user.last_name }}!
                    <p>
                        Here are your questions for the game
                        {{ game.host[0].first_name }} {{ game.host[0].last_name }} is hosting at {{ game.location }}
                        on {{ formatDate(game.date_time) }}.

                        <div class="mt-3">Your answers become the trivia.</div>
                    </p>
                </div>
        <div class="ml-4 mb-4 text-red-700" v-if="error">{{ error }}</div>
        <form @submit.prevent="submitAnswers">
            <!-- Render each question -->
            <div v-for="question in questions" :key="question.id" class="mb-4">

                <InputLabel :for="'question-' + question.id" :value="question.question_text" class="text-lg"/>

                <!-- Render input based on question type -->
                <template v-if="question.question_type === 'text'">
                    <TextInput
                        :id="'question-' + question.id"
                        type="text"
                        v-model="form.answers[question.id]"
                        required
                        class="mt-1 block w-full"
                    />
                </template>

                <template v-else-if="question.question_type === 'date'">
                    <div class="flex">
                    <!-- Month Select -->
                    <select
                        :id="'month-' + question.id"
                        v-model="questionDateValues[question.id].selectedMonth"
                        class="mt-1 block w-34 text-black"
                    >
                        <option v-for="(month, index) in months" :key="index" :value="index + 1">
                        {{ month }}
                        </option>
                    </select>

                    <!-- Day Select -->
                    <select
                        :id="'day-' + question.id"
                        v-model="questionDateValues[question.id].selectedDay"
                        class="mt-1 block w-24 text-black ml-2"
                    >
                        <option v-for="day in daysInMonth(question.id)" :key="day" :value="day">
                        {{ day }}
                        </option>
                    </select>

                    <!-- Year Select -->
                    <select
                        :id="'year-' + question.id"
                        v-model="questionDateValues[question.id].selectedYear"
                        class="mt-1 block w-32 text-black ml-2"
                    >
                        <option v-for="year in years" :key="year" :value="year">
                        {{ year }}
                        </option>
                    </select>
                    </div>
                </template>

                <InputError :message="form.errors['answers.' + question.id]" class="mt-2" />
            </div>

            <!-- Submit button -->

            <div class="mt-4">
                <PrimaryButton type="submit"  class="mt-2" >Save Answers</PrimaryButton>

                <SecondaryButton type="button" class="mt-2 ml-4" v-if="$page.props.auth.user" @click="cancel">Cancel</SecondaryButton>
            </div>

        </form>
    </QuestionsLayout>
</template>
