# Blogtec Features Manager

**Version:** 1.0.0  
**Author:** Alipio Gabriel  
**License:** GPL2  

## Description
The **Blogtec Features Manager** plugin is a custom WordPress plugin designed to manage various features for Blogtec.io, with the initial focus on **Pricing Table** management. The plugin is modular, allowing you to toggle the pricing table feature on or off from the WordPress admin settings.

### Key Features:
- **Pricing Table Management**: Create, update, and delete pricing tables for different content categories.
- **Admin Settings Page**: Toggle the Pricing Table feature on/off via an easy-to-use settings page.
- **Predefined Categories**: Automatically initializes a default "SEO Content" category with predefined word count ranges and prices.
- **User-Friendly UI**: Manage categories and pricing tables from the WordPress admin dashboard.
- **Modular Structure**: Enables seamless integration of additional features in future updates.

## Installation
1. Clone or download the plugin into your WordPress plugins directory (`/wp-content/plugins/`).
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to **Blogtec Features** in the admin menu to configure settings and enable the Pricing Table feature.

## Usage
1. Once activated, go to **Blogtec Features Settings** in the WordPress admin dashboard to enable or disable the Pricing Table feature.
2. After enabling the Pricing Table, a new menu item called **Pricing Table** will appear in the admin dashboard.
3. Add new categories, manage pricing for different word count ranges, and delete unnecessary rows or categories.

### Admin Settings
- **Enable Pricing Table Feature**: Toggle the Pricing Table functionality from the settings page under **Blogtec Features Settings**.
  
### Pricing Table Management
- **Add New Category**: Allows you to create new pricing categories. Default word count ranges and prices will automatically populate for new categories.
- **Delete Category**: Remove categories and their associated pricing data.
- **Edit Pricing**: Modify pricing for word count ranges within each category.

## Custom Database Tables
- The plugin creates two custom database tables:
  - **wp_blogtec_pricing**: Stores word count ranges, prices, and category IDs.
  - **wp_blogtec_pricing_categories**: Stores pricing categories.
  
## Changelog
### 1.0.0
- Initial release with Pricing Table functionality.

## License
This plugin is licensed under the GPL2 license.

---

### Author
**Alipio Gabriel**  
[Blogtec.io](https://blogtec.io)

