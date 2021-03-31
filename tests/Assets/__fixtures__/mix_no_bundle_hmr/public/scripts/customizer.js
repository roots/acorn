/*
 * ATTENTION: An "eval-source-map" devtool has been used.
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file with attached SourceMaps in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
(self["webpackChunk"] = self["webpackChunk"] || []).push([["/scripts/customizer"],{

/***/ "./resources/scripts/customizer.js":
/*!*****************************************!*\
  !*** ./resources/scripts/customizer.js ***!
  \*****************************************/
/***/ (function(__unused_webpack_module, __unused_webpack_exports, __webpack_require__) {

eval("/* provided dependency */ var $ = __webpack_require__(/*! jquery */ \"jquery\");\n/**\n * This file allows you to add functionality to the Theme Customizer\n * live preview. jQuery is readily available.\n *\n * {@link https://codex.wordpress.org/Theme_Customization_API}\n */\n\n/**\n * Change the blog name value.\n *\n * @param {string} value\n */\nwp.customize('blogname', function (value) {\n  value.bind(function (to) {\n    return $('.brand').text(to);\n  });\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvc2NyaXB0cy9jdXN0b21pemVyLmpzPzg0ODMiXSwibmFtZXMiOlsid3AiLCJ2YWx1ZSIsIiQiXSwibWFwcGluZ3MiOiI7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBQSxFQUFFLENBQUZBLHNCQUF5QixpQkFBUztBQUNoQ0MsT0FBSyxDQUFMQSxLQUFXLGNBQUU7QUFBQSxXQUFJQyxDQUFDLENBQURBLFFBQUMsQ0FBREEsTUFBSixFQUFJQSxDQUFKO0FBQWJEO0FBREZEIiwiZmlsZSI6Ii4vcmVzb3VyY2VzL3NjcmlwdHMvY3VzdG9taXplci5qcy5qcyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogVGhpcyBmaWxlIGFsbG93cyB5b3UgdG8gYWRkIGZ1bmN0aW9uYWxpdHkgdG8gdGhlIFRoZW1lIEN1c3RvbWl6ZXJcbiAqIGxpdmUgcHJldmlldy4galF1ZXJ5IGlzIHJlYWRpbHkgYXZhaWxhYmxlLlxuICpcbiAqIHtAbGluayBodHRwczovL2NvZGV4LndvcmRwcmVzcy5vcmcvVGhlbWVfQ3VzdG9taXphdGlvbl9BUEl9XG4gKi9cblxuLyoqXG4gKiBDaGFuZ2UgdGhlIGJsb2cgbmFtZSB2YWx1ZS5cbiAqXG4gKiBAcGFyYW0ge3N0cmluZ30gdmFsdWVcbiAqL1xud3AuY3VzdG9taXplKCdibG9nbmFtZScsIHZhbHVlID0+IHtcbiAgdmFsdWUuYmluZCh0byA9PiAkKCcuYnJhbmQnKS50ZXh0KHRvKSk7XG59KTtcbiJdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./resources/scripts/customizer.js\n");

/***/ }),

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/***/ (function(module) {

"use strict";
module.exports = window["jQuery"];

/***/ })

},
0,[["./resources/scripts/customizer.js","/scripts/manifest"]]]);