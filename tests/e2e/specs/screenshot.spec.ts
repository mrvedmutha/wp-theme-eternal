import { test, expect } from '../fixtures';

/**
 * This test allows taking screenshots of specific pages or elements for regression testing.
 * It uses environment variables to determine the target URL and optional selector.
 *
 * Environment Variables:
 * - SCREENSHOT_URL: The relative URL to navigate to (default: '/')
 * - SCREENSHOT_SELECTOR: Optional CSS selector to screenshot a specific element
 * - SCREENSHOT_NAME: The name of the screenshot file (default: 'screenshot.png')
 *
 * Example usage:
 * SCREENSHOT_URL=/about SCREENSHOT_SELECTOR=.site-header SCREENSHOT_NAME=header.png npm run test:screenshot
 */
test( 'capture screenshot', async ( { page } ) => {
	const url = process.env.SCREENSHOT_URL || '/';
	const selector = process.env.SCREENSHOT_SELECTOR;
	const name = process.env.SCREENSHOT_NAME || 'screenshot.png';

	await page.goto( url );

	// Wait for network to be idle to ensure all assets are loaded
	await page.waitForLoadState( 'networkidle' );

	if ( selector ) {
		const element = page.locator( selector ).first();
		await expect( element ).toBeVisible();
		await expect( element ).toHaveScreenshot( name );
	} else {
		await expect( page ).toHaveScreenshot( name, {
			fullPage: true,
		} );
	}
} );
