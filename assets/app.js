import './bootstrap.js';
import $ from 'jquery';  // Import jQuery
global.$ = global.jQuery = $;  // Expose $ and jQuery globally
// import $ from 'jquery';  // Import jQuery
// import 'select2';  // Import select2 (it depends on jQuery)
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
