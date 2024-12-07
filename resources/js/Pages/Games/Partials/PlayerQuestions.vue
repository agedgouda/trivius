<script setup>
import { ref,reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';

// Import components
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';

// Props for game and questions
const props = defineProps({
    game: Object,
    questions: Array,
    answers: Array,
});

// Create a reactive form object using useForm
const form = useForm({
    answers: {}, // Store answers keyed by question ID
});

// Populate form.answers based on the answers prop
if (props.answers && props.answers.length > 0) {
    const initialAnswers = {};
    props.answers.forEach(answer => {
        initialAnswers[answer.question_id] = answer.answer;
    });
    form.answers = initialAnswers; // Replace form.answers with a reactive object
}

// Submit answers function
const submitAnswers = () => {
    form.post(route('games.answers', { game: props.game.id }), {
        onSuccess: () => {
            console.log('Answers submitted successfully!');
        },
        onError: (errors) => {
            console.error('Submission errors:', errors);
        },
    });
};
</script>

<template>
    <div>
        <form @submit.prevent="submitAnswers">
            <!-- Render each question -->
            <div v-for="question in questions" :key="question.id" class="mb-4 ml-4">
                <InputLabel :for="'question-' + question.id" :value="question.question_text" />

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
                    <TextInput
                        :id="'question-' + question.id"
                        type="date"
                        v-model="form.answers[question.id]"
                        required
                        class="mt-1 block w-96"
                    />
                </template>

                <InputError :message="form.errors['answers.' + question.id]" class="mt-2" />
            </div>

            <!-- Submit button -->
            <PrimaryButton type="submit" class="mt-4">Submit</PrimaryButton>
        </form>
    </div>
</template>
