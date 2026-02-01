<template>
  <InstallerLayout current-step="admin">
    <div class="p-12">
      <h2 class="text-3xl font-bold text-white mb-6">Create Admin Account</h2>
      
      <p class="text-gray-300 mb-8">
        Create your administrator account to manage the game.
      </p>

      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Username -->
        <div>
          <label class="block text-sm font-semibold text-gray-300 mb-2">Username</label>
          <input 
            v-model="form.username"
            type="text"
            required
            minlength="3"
            maxlength="50"
            class="w-full px-4 py-3 bg-white/10 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="admin"
          />
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-semibold text-gray-300 mb-2">Email Address</label>
          <input 
            v-model="form.email"
            type="email"
            required
            class="w-full px-4 py-3 bg-white/10 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="admin@example.com"
          />
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-semibold text-gray-300 mb-2">Password</label>
          <input 
            v-model="form.password"
            type="password"
            required
            minlength="8"
            class="w-full px-4 py-3 bg-white/10 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="••••••••"
          />
          <p class="text-gray-400 text-sm mt-1">Minimum 8 characters</p>
        </div>

        <!-- Password Confirmation -->
        <div>
          <label class="block text-sm font-semibold text-gray-300 mb-2">Confirm Password</label>
          <input 
            v-model="form.password_confirmation"
            type="password"
            required
            minlength="8"
            class="w-full px-4 py-3 bg-white/10 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="••••••••"
          />
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-500/20 border border-red-500 rounded-lg p-4">
          <p class="text-red-300">{{ error }}</p>
        </div>

        <!-- Success Message -->
        <div v-if="success" class="bg-green-500/20 border border-green-500 rounded-lg p-4">
          <p class="text-green-300">{{ success }}</p>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between pt-6">
          <router-link 
            to="/install/settings"
            class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-3 rounded-lg transition"
          >
            ← Back
          </router-link>
          <button 
            type="submit"
            :disabled="submitting"
            class="bg-purple-600 hover:bg-purple-700 disabled:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition"
          >
            {{ submitting ? 'Creating Account...' : 'Create Account →' }}
          </button>
        </div>
      </form>
    </div>
  </InstallerLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import InstallerLayout from './InstallerLayout.vue';
import axios from 'axios';

const router = useRouter();

const form = ref({
  username: '',
  email: '',
  password: '',
  password_confirmation: ''
});

const submitting = ref(false);
const error = ref('');
const success = ref('');

const submitForm = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match';
    return;
  }

  submitting.value = true;
  error.value = '';
  success.value = '';

  try {
    const response = await axios.post('/install/setup-admin', form.value);
    success.value = 'Admin account created successfully!';
    setTimeout(() => {
      router.push('/install/install');
    }, 1000);
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create admin account';
  } finally {
    submitting.value = false;
  }
};
</script>
