<template>
  <InstallerLayout current-step="requirements">
    <div class="p-12">
      <h2 class="text-3xl font-bold text-white mb-6">System Requirements</h2>
      
      <div v-if="loading" class="text-center py-8">
        <div class="text-white">Checking requirements...</div>
      </div>

      <div v-else class="space-y-6">
        <!-- PHP Version -->
        <div class="bg-white/5 rounded-xl p-6">
          <h3 class="text-xl font-semibold text-white mb-4">PHP Version</h3>
          <div class="flex items-center justify-between">
            <span class="text-gray-300">PHP {{ requirements.php?.version }}</span>
            <span v-if="requirements.php?.status" class="text-green-400">✓ Required: {{ requirements.php?.required }}+</span>
            <span v-else class="text-red-400">✗ Required: {{ requirements.php?.required }}+</span>
          </div>
        </div>

        <!-- PHP Extensions -->
        <div class="bg-white/5 rounded-xl p-6">
          <h3 class="text-xl font-semibold text-white mb-4">PHP Extensions</h3>
          <div class="grid grid-cols-2 gap-4">
            <div v-for="(status, extension) in requirements.extensions" :key="extension" class="flex items-center justify-between">
              <span class="text-gray-300">{{ extension }}</span>
              <span v-if="status" class="text-green-400">✓</span>
              <span v-else class="text-red-400">✗</span>
            </div>
          </div>
        </div>

        <!-- Directory Permissions -->
        <div class="bg-white/5 rounded-xl p-6">
          <h3 class="text-xl font-semibold text-white mb-4">Directory Permissions</h3>
          <div class="space-y-3">
            <div v-for="(status, path) in permissions" :key="path" class="flex items-center justify-between">
              <span class="text-gray-300">{{ path }}</span>
              <span v-if="status" class="text-green-400">✓ Writable</span>
              <span v-else class="text-red-400">✗ Not writable</span>
            </div>
          </div>
        </div>

        <!-- Status Message -->
        <div v-if="!allRequirementsMet" class="bg-red-500/20 border border-red-500 rounded-xl p-6">
          <p class="text-red-300">
            ⚠️ Some requirements are not met. Please fix the issues above before continuing.
          </p>
        </div>

        <div v-else class="bg-green-500/20 border border-green-500 rounded-xl p-6">
          <p class="text-green-300">
            ✓ All requirements met! You can proceed to the next step.
          </p>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between pt-6">
          <router-link 
            to="/install"
            class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-3 rounded-lg transition"
          >
            ← Back
          </router-link>
          <router-link 
            v-if="allRequirementsMet"
            to="/install/database"
            class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition"
          >
            Continue →
          </router-link>
          <button 
            v-else
            @click="checkRequirements"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition"
          >
            Recheck Requirements
          </button>
        </div>
      </div>
    </div>
  </InstallerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import InstallerLayout from './InstallerLayout.vue';
import axios from 'axios';

const loading = ref(true);
const requirements = ref({});
const permissions = ref({});
const allRequirementsMet = ref(false);

const checkRequirements = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/install/requirements');
    requirements.value = response.data.requirements;
    permissions.value = response.data.permissions;
    allRequirementsMet.value = response.data.allRequirementsMet;
  } catch (error) {
    console.error('Error checking requirements:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  checkRequirements();
});
</script>
