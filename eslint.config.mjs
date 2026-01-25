import htmlPlugin from 'eslint-plugin-html';
import globals from 'globals';
import js from '@eslint/js';

const inlinePHP = {
	meta: {
		name: "process-inline-php-plugin",
		version: "1.2.3",
	},
	processors: {
		"strip-inline-php": {
			meta: {
				name: "inline-php-plugin-processor",
				version: "1.2.3",
			},
			preprocess: (text, filename) => [
				text.replace(/<\?(?:php)?(.*?)\?>/g, (match, phpContent) => {
					// If the PHP tag is inside an object or array (we check for the presence of a key)
					if (/[\w\-]+\s*:/.test(match)) {
						// Inside an object or array, replace the PHP code with key assigned `undefined`
						return '__eslint_value: undefined';
					}

					// If the PHP code is standalone, such as in `value = <?=phpstuff?>;`, replace with `undefined`
					return 'undefined'; // Or 'null', or another suitable placeholder
				})
			],
			postprocess(messages, filename) {
				// `messages` argument contains two-dimensional array of Message objects
				// where each top-level array item contains array of lint messages related
				// to the text that was returned in array from preprocess() method

				// you need to return a one-dimensional array of the messages you want to keep
				return [].concat(...messages);
			},
		}
	},
};

export default [
  {
    files: ["app/Views/**/*.php"],
    plugins: {
      html: htmlPlugin, inlinePHP,
    },
	processor: "inlinePHP/strip-inline-php",
    languageOptions: {
      globals: globals.browser,
    },
	rules: {
		/*"brace-style": ["error", "1tbs"],*/     		// One true brace style ["1tbs", "allman", "stroustrup"] https://eslint.org/docs/latest/rules/brace-style
		/*/*"object-curly-spacing": ["error", "always"],*/  	// Enforce spaces inside curly braces for objects
		/*"curly": ["error", "all"],*/
		/*"semi": ["error", "always"],*/
		"no-console": ["warn"],
		"no-unused-vars": ["warn",
			{
				"argsIgnorePattern": "^xhr$|^status$|^error$|^ui$|^event$"
			}
		],
		"prefer-const": "error",
		"no-var": "error",
		"quotes": ["error", "single"],
		"no-multi-spaces": ["error", { "ignoreEOLComments": true }],
		"max-len": ["warn", { "code": 100 }],
		"object-curly-spacing": ["error", "always"],  	// Enforce spaces inside curly braces for objects
		"space-in-parens": ["error", "never"],      	// No spaces inside parentheses
		"array-bracket-spacing": ["error", "never"],  	// No spaces inside array brackets
		"no-trailing-spaces": "error",             		// Disallow trailing spaces at the end of lines
		"eqeqeq": ["error", "always"],
		/*"no-alert": "warn",*/
		"no-eval": "error",
		"no-implied-eval": "error",
		"consistent-return": "error",
		"no-magic-numbers": ["warn", { "ignore": [0, 1, -1] }],
		"no-empty-function": "warn",
		"no-else-return": "warn",
		"no-use-before-define": ["error", { "functions": false, "classes": true }],
		"no-nested-ternary": "warn",
		"max-lines": ["warn", 500],
		"no-const-assign": "error",
	}
  },
];