var gulp = require('gulp'),
    elixir = require('laravel-elixir'),
    plugins = require('gulp-load-plugins')();

require('laravel-elixir-vue');

elixir(function(mix) {
    mix.webpack([
        './resources/assets/src/fieldtype.js'
    ], './resources/assets/js/fieldtype.js');
    mix.webpack([
        './resources/assets/src/scripts.js'
    ], './resources/assets/js/scripts.js');
});
