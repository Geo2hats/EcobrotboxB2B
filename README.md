# Ecobrotbox B2B Plugin

**Version:** v1.0.3
**License:** Proprietary
**Type:** Shopware Platform Plugin

## Overview

The Ecobrotbox B2B plugin provides business-to-business functionality including customer group management, registration approval workflows, checkout restrictions, and B2B-specific pricing and features.

---

## Table of Contents

1. [Customer Registration & Approval](#customer-registration--approval)
2. [Customer Group Management](#customer-group-management)
3. [Checkout Restrictions](#checkout-restrictions)
4. [B2B Pricing & Display](#b2b-pricing--display)
5. [Account Type Handling](#account-type-handling)
6. [Configuration Options](#configuration-options)
7. [Technical Details](#technical-details)
8. [Installation & Usage](#installation--usage)

---

## Customer Registration & Approval

### 1. Registration Approval Workflow
- **Feature:** Manual approval required for new customer registrations
- **Implementation:** `Registration` subscriber
- **Functionality:**
  - Automatic account deactivation on registration
  - Separate configuration for private and business accounts
  - Customer group assignment based on account type
  - Manual activation by administrators
  - Registration confirmation messages
  - Redirect after registration

### 2. Account Deactivation
- **Feature:** Automatic deactivation of new registrations
- **Configuration Options:**
  - `privateDeactivateAccount` - Deactivate private accounts
  - `businessDeactivateAccount` - Deactivate business accounts
- **Functionality:**
  - Deactivates account immediately after registration
  - Shows message to customer about manual review
  - Prevents login until administrator approval
  - Logs deactivation events

---

## Customer Group Management

### 3. Customer Group Assignment
- **Feature:** Automatic customer group assignment
- **Configuration:**
  - `privateNewCustomerGroup` - Group for new private customers
  - `businessNewCustomerGroup` - Group for new business customers
- **Functionality:**
  - Assigns customers to groups based on account type
  - Supports different groups for private and business
  - Automatic assignment during registration
  - Group-based feature access

---

## Checkout Restrictions

### 4. Checkout Access Control
- **Feature:** Restrict checkout to specific customer groups
- **Implementation:** `Checkout` subscriber
- **Functionality:**
  - Blocks checkout for unauthorized customer groups
  - Configurable allowed customer groups
  - Redirects blocked users with message
  - Integration with checkout page events
  - Multi-language error messages

### 5. Payment Method Restrictions
- **Feature:** B2B-specific payment method handling
- **Functionality:**
  - Custom payment method display
  - Group-based payment method availability
  - B2B payment terms integration
  - Invoice payment support

---

## B2B Pricing & Display

### 6. Price Display & Replacement
- **Feature:** B2B-specific pricing display
- **Implementation:** Price component customization
- **Functionality:**
  - Shows "Price after login" for non-logged users
  - B2B price display for logged-in business customers
  - Price unit display customization
  - Group-based price visibility

### 7. Product Price Components
- **Feature:** Custom price display in product cards
- **Views:**
  - `b2b/price.html.twig` - B2B price display
  - `product/card/price-unit.html.twig` - Price unit display
- **Functionality:**
  - Conditional price display
  - Login-required price messaging
  - B2B price formatting

---

## Account Type Handling

### 8. Private vs Business Account Management
- **Feature:** Different handling for private and business accounts
- **Implementation:** Account type detection
- **Functionality:**
  - Separate registration workflows
  - Different customer group assignment
  - Separate deactivation settings
  - Account type-based feature access
  - Registration form customization

---

## Configuration Options

### 9. System Configuration
- **Location:** Administration → Settings → System → Plugins → Ecobrotbox B2B
- **Configuration Options:**
  - **Private New Customer Group:** Customer group for new private registrations
  - **Business New Customer Group:** Customer group for new business registrations
  - **Private Deactivate Account:** Auto-deactivate private accounts
  - **Business Deactivate Account:** Auto-deactivate business accounts
  - **Allowed Customer Groups for Checkout:** Groups that can access checkout
- **Functionality:**
  - Per-sales-channel configuration
  - Multi-language support (DE/EN)

---

## Technical Details

### 10. Subscribers & Event Handling
- **Subscribers:**
  - `Registration` - Handles customer registration events
  - `Checkout` - Manages checkout access control
  - `DisableFunctions` - Disables specific functions for B2B
- **Events:**
  - `CustomerRegisterEvent` - Registration handling
  - `CheckoutPageLoadedEvent` - Checkout access validation
  - Various frontend events

### 11. Storefront Integration
- **Views:**
  - `b2b/price.html.twig` - B2B price display
  - `buy-widget/buy-widget-form.html.twig` - Buy widget customization
  - `product/card/action.html.twig` - Product card actions
  - `address/address-personal.html.twig` - Address form customization
- **Functionality:**
  - Conditional rendering based on customer group
  - B2B-specific UI elements
  - Login requirement messaging

---

## Installation & Usage

### Usage

#### For Administrators:
1. Configure customer groups for new registrations
2. Set up account approval workflow
3. Configure checkout access restrictions
4. Review and activate new customer registrations

#### For Customers:
1. Register as private or business customer
2. Wait for administrator approval
3. Access B2B features after approval
4. View B2B pricing after login

---

## Security Features

- Account deactivation on registration
- Manual approval workflow
- Customer group-based access control
- Checkout access restrictions
- Secure registration handling

---

## Dependencies

- Shopware Core Framework 6.7.*
- Shopware Storefront 6.7.*
- Symfony Components

---

