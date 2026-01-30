import { ref, watch, onMounted } from 'vue';

const isDark = ref(false);

export function useDarkMode() {
    const initDarkMode = () => {
        // Check localStorage first
        const stored = localStorage.getItem('darkMode');
        if (stored !== null) {
            isDark.value = stored === 'true';
        } else {
            // Check system preference
            isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        applyTheme();
    };

    const applyTheme = () => {
        if (isDark.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };

    const toggleDarkMode = () => {
        isDark.value = !isDark.value;
        localStorage.setItem('darkMode', isDark.value.toString());
        applyTheme();
    };

    watch(isDark, applyTheme);

    onMounted(() => {
        initDarkMode();
    });

    return {
        isDark,
        toggleDarkMode,
        initDarkMode,
    };
}
