import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// AI Studio Manager
import { initAiStudio, AiStudioManager } from './ai-studio';
window.initAiStudio = initAiStudio;
window.AiStudioManager = AiStudioManager;
