---
description: Step-by-step recipe for creating a new PHP component in WP Rig.
globs: inc/**/*.php, functions.php
---

# Create a New WP Rig Component

This skill provides the recipe to scaffold and register a new theme component.

## Step 1: Use the Scaffolding Script

WP Rig provides a dedicated script to create the component directory and initial class file.

```bash
npm run create-rig-component "Your Feature Name"
```

This will:
1. Create a folder in `inc/Your_Feature_Name/`.
2. Generate `Component.php` inside it, implementing `Component_Interface`.

## Step 2: Register the Component

After scaffolding, the component must be registered in the main theme class.

1. Open `inc/Theme.php`.
2. Locate the `get_default_components()` method.
3. Add your new component class to the array.

**Example:**
```php
protected function get_default_components() : array {
	$components = array(
		// ... existing components
		new Your_Feature_Name\Component(),
	);

	return $components;
}
```

## Step 3: Implement Hooks

In your new `inc/Your_Feature_Name/Component.php`, use the `initialize()` method to add WordPress hooks.

```php
public function initialize() {
	add_action( 'wp_enqueue_scripts', array( $this, 'action_enqueue_scripts' ) );
}
```

## Best Practices

- Always use `npm run create-rig-component` instead of manual creation.
- Ensure the namespace matches `WP_Rig\WP_Rig\{Feature}`.
- Implement only the interfaces you need (e.g., `Templating_Component_Interface` if you provide template tags).
