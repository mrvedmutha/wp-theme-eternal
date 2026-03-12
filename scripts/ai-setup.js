/**
 * AI Setup Script for WP Rig
 *
 * This script optimizes WP Rig for agentic discoverability by adding
 * necessary configuration and instruction files for various coding agents.
 */

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import inquirer from 'inquirer';
import { Command } from 'commander';
import colors from 'ansi-colors';

const __dirname = path.dirname( fileURLToPath( import.meta.url ) );
const rootDir = path.resolve( __dirname, '..' );

const program = new Command();

const AGENTS = [
	{
		name: 'Claude Code',
		value: 'claude',
		files: [
			{ source: 'AGENTS.md', target: 'CLAUDE.md' },
			{ source: '.aiignore', target: '.claudeignore' },
		],
	},
	{
		name: 'Cursor',
		value: 'cursor',
		files: [
			{ source: 'AGENTS.md', target: '.cursorrules' },
			{ source: '.aiignore', target: '.cursorignore' },
		],
	},
	{
		name: 'Windsurf',
		value: 'windsurf',
		files: [
			{ source: 'AGENTS.md', target: '.windsurfrules' },
			{ source: '.aiignore', target: '.windsurfignore' },
		],
	},
	{
		name: 'Roo Code (Cline)',
		value: 'roo',
		files: [
			{ source: 'AGENTS.md', target: '.clinerules' },
			{ source: '.aiignore', target: '.clineignore' },
		],
	},
	{
		name: 'JetBrains Junie',
		value: 'junie',
		files: [
			{ source: 'AGENTS.md', target: 'JUNIE.md' },
			{ source: '.aiignore', target: '.junieignore' },
		],
	},
	{
		name: 'Gemini CLI',
		value: 'gemini',
		files: [
			{ source: 'AGENTS.md', target: 'GEMINI.md' },
			{ source: '.aiignore', target: '.geminiignore' },
		],
	},
	{
		name: 'GitHub Copilot',
		value: 'copilot',
		files: [
			{ source: 'AGENTS.md', target: '.github/copilot-instructions.md' },
			{ source: '.aiignore', target: '.copilotignore' },
		],
	},
	{
		name: 'Aider',
		value: 'aider',
		files: [
			{ source: 'AGENTS.md', target: '.aider.instructions.md' },
			{ source: '.aiignore', target: '.aiderignore' },
		],
	},
	{
		name: 'OpenCode / OpenHands',
		value: 'opencode',
		files: [
			{ source: 'AGENTS.md', target: 'OPENCODE.md' },
			{ source: 'AGENTS.md', target: '.openhands_instructions' },
			{ source: '.aiignore', target: '.opencodeignore' },
			{ source: '.aiignore', target: '.openhands_ignore' },
		],
	},
	{
		name: 'Mastra',
		value: 'mastra',
		files: [
			{ source: 'AGENTS.md', target: 'MASTRA.md' },
			{ source: '.aiignore', target: '.mastraignore' },
		],
	},
	{
		name: 'PearAI',
		value: 'pearai',
		files: [
			{ source: 'AGENTS.md', target: '.pearrules' },
			{ source: '.aiignore', target: '.pearignore' },
		],
	},
	{
		name: 'OpenAI Codex',
		value: 'openai',
		files: [
			{ source: 'AGENTS.md', target: 'OPENAI.md' },
			{ source: '.aiignore', target: '.openaiignore' },
		],
	},
];

/**
 * Ensure .aiignore exists or warn if missing.
 */
function checkAiIgnore() {
	const aiIgnorePath = path.join( rootDir, '.aiignore' );
	if ( ! fs.existsSync( aiIgnorePath ) ) {
		console.warn(
			colors.yellow(
				'⚠️ Base .aiignore file not found in theme root. Please create it or restore it from the template in docs/AI-SETUP-GUIDE.md.'
			)
		);
	}
}

/**
 * Copy file from source to target.
 *
 * @param {string[]} selectedAgents Selected agents to setup.
 * @param {boolean}  force          Whether to force overwrite.
 */
async function setupFiles( selectedAgents, force = false ) {
	checkAiIgnore();

	const agentsToSetup = AGENTS.filter( ( agent ) =>
		selectedAgents.includes( agent.value )
	);

	for ( const agent of agentsToSetup ) {
		console.log( colors.green( `\nSetting up for ${ agent.name }...` ) );

		for ( const file of agent.files ) {
			const sourcePath = path.join( rootDir, file.source );
			const targetPath = path.join( rootDir, file.target );

			// Ensure target directory exists (e.g. for .github/...)
			const targetDir = path.dirname( targetPath );
			if ( ! fs.existsSync( targetDir ) ) {
				fs.mkdirSync( targetDir, { recursive: true } );
			}

			if ( ! fs.existsSync( sourcePath ) ) {
				console.warn(
					colors.yellow(
						`⚠️ Source file ${ file.source } not found. Skipping ${ file.target }.`
					)
				);
				continue;
			}

			if ( fs.existsSync( targetPath ) && ! force ) {
				const { overwrite } = await inquirer.prompt( [
					{
						type: 'confirm',
						name: 'overwrite',
						message: `File ${ file.target } already exists. Overwrite?`,
						default: false,
					},
				] );

				if ( ! overwrite ) {
					console.log( colors.gray( `  Skipped ${ file.target }` ) );
					continue;
				}
			}

			fs.copyFileSync( sourcePath, targetPath );
			console.log( colors.cyan( `  ✓ Created ${ file.target }` ) );
		}
	}
}

program
	.name( 'ai-setup' )
	.description( 'Setup WP Rig for specific AI coding agents' )
	.option( '-a, --all', 'Setup all supported agents' )
	.option( '-f, --force', 'Force overwrite existing files', false )
	.option(
		'-n, --non-interactive',
		'Run without prompts (requires --all or specific agents via CLI)',
		false
	)
	.argument( '[agents...]', 'Specific agents to setup' )
	.action( async ( agents, options ) => {
		let selectedAgents = agents;

		if ( options.all ) {
			selectedAgents = AGENTS.map( ( a ) => a.value );
		}

		if ( selectedAgents.length === 0 ) {
			if ( options.nonInteractive ) {
				console.error(
					colors.red(
						'Error: No agents specified in non-interactive mode. Use --all or provide agent names.'
					)
				);
				process.exit( 1 );
			}

			const { selection } = await inquirer.prompt( [
				{
					type: 'checkbox',
					name: 'selection',
					message: 'Select the coding agents you want to support:',
					choices: AGENTS,
					validate: ( input ) =>
						input.length > 0 || 'Select at least one agent.',
				},
			] );
			selectedAgents = selection;
		}

		await setupFiles( selectedAgents, options.force );

		console.log( colors.bold.green( '\nAI setup complete!' ) );
		console.log(
			'Refer to AGENTS.md and .aiignore for the source of these configurations.'
		);
	} );

program.parse();
