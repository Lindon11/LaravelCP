<template>
  <InstallerLayout current-step="install">
    <div class="p-12">
      <h2 class="text-3xl font-bold text-white mb-6">Installation</h2>
      
      <p v-if="!installing && !completed" class="text-gray-300 mb-8">
        Ready to install? Click the button below to begin the installation process.
      </p>

      <!-- Installation Progress -->
      <div v-if="installing || completed" class="space-y-4 mb-8">
        <div v-for="step in installSteps" :key="step.id" class="bg-white/5 rounded-lg p-4 flex items-center justify-between">
          <div class="flex items-center">
            <div v-if="step.status === 'pending'" class="w-6 h-6 rounded-full border-2 border-gray-500 mr-3"></div>
            <div v-else-if="step.status === 'running'" class="w-6 h-6 rounded-full border-2 border-purple-500 border-t-transparent animate-spin mr-3"></div>
            <div v-else-if="step.status === 'completed'" class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center mr-3">
              <span class="text-white text-xs">✓</span>
            </div>
            <div v-else-if="step.status === 'error'" class="w-6 h-6 rounded-full bg-red-500 flex items-center justify-center mr-3">
              <span class="text-white text-xs">✗</span>
            </div>
            <span class="text-white">{{ step.label }}</span>
          </div>
          <span v-if="step.status === 'completed'" class="text-green-400 text-sm">Completed</span>
          <span v-else-if="step.status === 'running'" class="text-purple-400 text-sm">Running...</span>
          <span v-else-if="step.status === 'error'" class="text-red-400 text-sm">Failed</span>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="bg-red-500/20 border border-red-500 rounded-lg p-4 mb-8">
        <p class="text-red-300 font-semibold mb-2">Installation Error:</p>
        <p class="text-red-300">{{ error }}</p>
      </div>

      <!-- Success Message -->
      <div v-if="completed && !error" class="bg-green-500/20 border border-green-500 rounded-lg p-4 mb-8">
        <p class="text-green-300">✓ Installation completed successfully!</p>
      </div>

      <!-- Navigation -->
      <div class="flex justify-between pt-6">
        <router-link 
          v-if="!installing && !completed"
          to="/install/setup-admin"
          class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-3 rounded-lg transition"
        >
          ← Back
        </router-link>
        <div v-else></div>
        
        <button 
          v-if="!installing && !completed"
          @click="startInstallation"
          class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition"
        >
          Start Installation
        </button>
        <router-link 
          v-else-if="completed && !error"
          to="/install/complete"
          class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition"
        >
          Continue →
        </router-link>
        <button 
          v-else-if="error"
          @click="startInstallation"
          class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition"
        >
          Retry Installation
        </button>
      </div>
    </div>
  </InstallerLayout>
</template>

<script setup>
import { ref } from 'vue';
import InstallerLayout from './InstallerLayout.vue';
import axios from 'axios';

const installing = ref(false);
const completed = ref(false);
const error = ref('');

const installSteps = ref([
  { id: 1, label: 'Clearing configuration cache', status: 'pending' },
  { id: 2, label: 'Running database migrations', status: 'pending' },
  { id: 3, label: 'Creating storage links', status: 'pending' },
  { id: 4, label: 'Finalizing installation', status: 'pending' }
]);

const updateStepStatus = (stepId, status) => {
  const step = installSteps.value.find(s => s.id === stepId);
  if (step) {
    step.status = status;
  }
};

const startInstallation = async () => {
  installing.value = true;
  completed.value = false;
  error.value = '';

  // Reset all steps
  installSteps.value.forEach(step => {
    step.status = 'pending';
  });

  try {
    // Simulate step-by-step installation
    for (let i = 0; i < installSteps.value.length; i++) {
      updateStepStatus(i + 1, 'running');
      
      if (i === 0) {
        // Just visual delay for first steps
        await new Promise(resolve => setTimeout(resolve, 1000));
      } else if (i === installSteps.value.length - 1) {
        // Actually run the installation on the last step
        const response = await axios.post('/install/install/process');
        if (!response.data.success) {
          throw new Error(response.data.message);
        }
      } else {
        await new Promise(resolve => setTimeout(resolve, 1500));
      }
      
      updateStepStatus(i + 1, 'completed');
    }

    completed.value = true;
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Installation failed';
    // Mark the current running step as error
    const runningStep = installSteps.value.find(s => s.status === 'running');
    if (runningStep) {
      runningStep.status = 'error';
    }
  } finally {
    installing.value = false;
  }
};
</script>
