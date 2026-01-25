const postcss = require('postcss');
const postcssHtml = require('postcss-html');

const inlinePHP = {
	parse(css, opts) {
		css = css.replace(/<\?(?:php)?(.*?)\?>/g, (match, phpContent, offset) => {
			const beforeMatch = css.slice(0, offset);
			const afterMatch = css.slice(offset + match.length);
			
			// Check if PHP is inside a rule
			if (beforeMatch.lastIndexOf('{') > beforeMatch.lastIndexOf('}') && afterMatch.indexOf('}') !== -1) {
				// Check if there is a property, meaning php is most likely outputting a value
				if (/[\w\-]+\s*:.*$/.test(beforeMatch)) {
					return 'initial;';
				}
			}
			
			// PHP code is standalone
			return '';
		});

		return postcssHtml.parse(css, opts);
	},

	stringify(node, builder) {
		postcssHtml.stringify(node, builder);
	}
};

module.exports = {	
	extends: 'stylelint-config-standard', 
	rules: {
		/*"plugin/ignore-php": true,*/
		"color-no-invalid-hex": true,
		"no-empty-source": null,
		"rule-empty-line-before": null,
		"selector-class-pattern": [
			"^([a-z][a-z0-9]*)(-[a-z0-9]+)*$",
			{
				"message": "[%s] class should use hypen delimiter: -"
			}
		],  // Kebab case for classes
		"selector-id-pattern": [
			"^([a-z][a-z0-9]*)(_[a-z0-9]+)*$",
			{
				"message": "[%s] id should use underscore delimiter: _"
			}
		],     // Snake case for ids
		"property-no-unknown": true,
	},
	overrides: [
	{
	  files: ["app/Views/**/*.php", "public/assets/css/*/*.css"],
	  customSyntax: inlinePHP,
	  //customSyntax: "postcss-html",
	},
	],
};