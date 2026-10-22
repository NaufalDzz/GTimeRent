import { initGlyphField } from './home/glyph-field';
import { initTypewriter } from './home/typewriter';

const typewriter = document.querySelector('[data-typewriter]');
if (typewriter) initTypewriter(typewriter);

const glyphField = document.querySelector('[data-glyph-field]');
if (glyphField) initGlyphField(glyphField);
