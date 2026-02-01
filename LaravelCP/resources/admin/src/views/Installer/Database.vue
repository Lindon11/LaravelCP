<template>
  <InstallerLayout current-step="database">
    <div class="p-12">
      <h2 class="text-3xl font-bold text-white mb-6">Database Configuration</h2>
      
      <p class="text-gray-300 mb-8">
        Enter your database connection details. We'll test the connection before proceeding.
      </p>

      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Database Host -->
        <div>
          <label class="block text-sm font-semibold text-gray-300 mb-2">Database Host</label>
          <input 
            v-model="form.db_host"
            type="text"
            required
            class="w-full px-4 py-3 bg-white/10 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="localhost"
          />
        </div>

        <!-- Database Port -->
        <div>
          <label class="block text-sm font-semibold text-gray-300 mb-2">Database Port</label>
          <input 
            v-model="form.db_port"
            type="number"
            required
            class="w-full px-4 py-3 bg-white/10 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="3306"
          />
        </div>

        <!-- Database Name -->
        <div>
          <label class="block text-sm font-semibold text-gray-300 mb-2">Database Name</label>
          <input 
            v-model="form.db_name"
            type="text"
            required
            class="w-full px-4 py-3 bg-white/10 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="gangster_legends"
          />
        </div>

        <!-- Database Username -->
        <div>
          <label class="block text-sm font-semibold text-gray-300 mb-2">Database Username</label>
          <input 
            v-model="form.db_username"
            type="text"
            required
            class="w-full px-4 py-3 bg-white/10 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="root"
          />
        </div>

        <!-- Database Password -->
        <div>
          <label class="block text-sm font-semibold text-gray-300 mb-2">Database Password</label>
          <input 
            v-model="form.db_password"
            type="password"
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
            to="/install/requirements"
            class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-3 rounded-lg transition"
          >
            ← Back
          </router-link>
          <button 
            type="submit"
            :disabled="submitting"
            class="bg-purple-600 hover:bg-purple-700 disabled:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition"
          >
            {{ submitting ? 'Testing Connection...' : 'Test & Continue →' }}
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
  db_host: 'localhost',
  db_port: 3306,
  db_name: '',
  db_username: 'root',
  db_password: ''
});

const submitting = ref(false);
const error = ref('');
const success = ref('');

const submitForm = async () => {
  submitting.value = true;
  error.value = '';
  success.value = '';

  try {
    const response = await axios.post('/install/database', form.value);
    success.value = 'Database connection successful!';
    setTimeout(() => {
      router.push('/install/settings');
    }, 1000);
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.errors?.db_connection || 'Database connection failed';
  } finally {
    submitting.value = false;
  }
};
</script>
