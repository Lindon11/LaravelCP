<template>
  <InstallerLayout current-step="settings">
    <div class="p-12">
      <h2 class="text-3xl font-bold text-white mb-6">Application Settings</h2>
      
      <p class="text-gray-300 mb-8">
        Configure your application's basic settings.
      </p>

      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Application Name -->
        <div>
          <label class="block text-sm font-semibold text-gray-300 mb-2">Application Name</label>
          <input 
            v-model="form.app_name"
            type="text"
            required
            class="w-full px-4 py-3 bg-white/10 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="Gangster Legends"
          />
          <p class="text-gray-400 text-sm mt-1">The name of your game that will appear throughout the application.</p>
        </div>

        <!-- Application URL -->
        <div>
          <label class="block text-sm font-semibold text-gray-300 mb-2">Application URL</label>
          <input 
            v-model="form.app_url"
            type="url"
            required
            class="w-full px-4 py-3 bg-white/10 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="https://yoursite.com"
          />
          <p class="text-gray-400 text-sm mt-1">The full URL where your application will be accessible (including http:// or https://).</p>
        </div>

        <!-- Environment -->
        <div>
          <label class="block text-sm font-semibold text-gray-300 mb-2">Environment</label>
          <select 
            v-model="form.app_env"
            required
            class="w-full px-4 py-3 bg-white/10 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
          >
            <option value="local">Local (Development)</option>
            <option value="production">Production (Live)</option>
          </select>
          <p class="text-gray-400 text-sm mt-1">Choose 'Local' for development/testing or 'Production' for live servers.</p>
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
            to="/install/database"
            class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-3 rounded-lg transition"
          >
            ← Back
          </router-link>
          <button 
            type="submit"
            :disabled="submitting"
            class="bg-purple-600 hover:bg-purple-700 disabled:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition"
          >
            {{ submitting ? 'Saving...' : 'Continue →' }}
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
  app_name: 'Gangster Legends',
  app_url: window.location.origin,
  app_env: 'production'
});

const submitting = ref(false);
const error = ref('');
const success = ref('');

const submitForm = async () => {
  submitting.value = true;
  error.value = '';
  success.value = '';

  try {
    const response = await axios.post('/install/settings', form.value);
    success.value = 'Settings saved successfully!';
    setTimeout(() => {
      router.push('/install/setup-admin');
    }, 1000);
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save settings';
  } finally {
    submitting.value = false;
  }
};
</script>
