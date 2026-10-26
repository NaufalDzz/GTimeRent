import { initGlyphField } from './home/glyph-field';
import { initTypewriter } from './home/typewriter';
import { initWorkspace } from './workspace';

initWorkspace();

const typewriter = document.querySelector('[data-typewriter]');
if (typewriter) initTypewriter(typewriter);

const glyphField = document.querySelector('[data-glyph-field]');
if (glyphField) initGlyphField(glyphField);
