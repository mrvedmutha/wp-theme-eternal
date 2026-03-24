module.exports = {
	extends: ['@commitlint/config-conventional'],
	rules: {
		'type-enum': [
			2,
			'always',
			[
				'feat',     // new feature
				'fix',      // bug fix
				'chore',    // maintenance, deps, config
				'refactor', // code change that neither fixes a bug nor adds a feature
				'style',    // CSS / formatting changes
				'docs',     // documentation only
				'test',     // adding or updating tests
				'build',    // build system or tooling changes
				'revert',   // revert a previous commit
			],
		],
		'subject-case': [2, 'always', 'lower-case'],
		'header-max-length': [2, 'always', 100],
	},
};
