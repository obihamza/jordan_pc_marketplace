<?php

namespace App\Services;

use App\Models\User;

class SiteGuide
{
    public static function for(?User $user): string
    {
        $guide = self::general();

        if ($user?->isAdmin()) {
            $guide .= "\n\n".self::admin();
        } elseif ($user?->isSeller()) {
            $guide .= "\n\n".self::seller();
        }

        return $guide;
    }

    private static function general(): string
    {
        return <<<'TXT'
WEBSITE GUIDE (JO-Marketplace)
- Marketplace in Jordan for new and used items. Currency: Jordanian dinar (JOD). Payment: cash on delivery only, nothing is paid online.
- Browsing: the home page and the Products page list products (search, category and price filters, infinite scroll). Click a product to see photos, description, seller, condition and reviews.
- Account: Register / Login in the top bar. Every non-admin account can buy.
- Buying: add products to the cart, open the cart, choose or add a delivery address, optionally enter a coupon code, then place the order. A coupon works only if it is active and the order reaches its minimum amount.
- Orders: "My orders" shows each order and its status: pending -> confirmed -> processing -> shipped -> delivered (or cancelled). A buyer can cancel an order only while it is still pending.
- Reviews: a buyer can review a product only after that order was delivered and paid. A review is shown publicly only after an admin approves it.
- Becoming a seller: log in, choose "Become a seller", and enter the official name, national ID number and a photo of the ID. The ID photo is private (only admins can see it). An admin reviews the application (pending -> approved or rejected). Once approved, the user gets the seller dashboard and can still buy as normal.
- Account details and addresses are managed from "My profile".
TXT;
    }

    private static function seller(): string
    {
        return <<<'TXT'
SELLER DASHBOARD GUIDE
- Sidebar: Dashboard (sales insight and charts), Products, Product images, Orders, Order items, Reviews, Assistant, My profile, View website.
- Add a product: Products page -> "Add product" opens a pop-up (name, brand, category, price, stock, condition, status, description, photos). A new category can be created from the Products page too.
- Product photos: click a photo to open its settings and mark it as the primary photo (shown first in the shop) or delete it.
- Orders to prepare: open Order items, and update each line's status (pending, confirmed, processing, shipped, delivered, cancelled). Cancelled lines are not counted in revenue.
- Low stock: products with 5 units or fewer are flagged on the dashboard.
- Reviews of the seller's products are moderated by admins, not by the seller.
- Seller details are edited inside "My profile".
TXT;
    }

    private static function admin(): string
    {
        return <<<'TXT'
ADMIN DASHBOARD GUIDE
- Sidebar: Dashboard (site-wide insight and charts), Users, Sellers, Categories, Products, Product images, Orders, Order items, Payments, Coupons, Reviews, Assistant, My profile, View website.
- Seller applications: Sellers page shows pending applications; view the ID photo, then approve or reject. A notification bell at the top shows new events (new applications, new reviews).
- Reviews: pending reviews are approved or rejected from the Reviews page; only approved reviews count in ratings.
- Coupons, categories, products, orders and payments are managed from their own pages with icon buttons (view, edit, delete). Dangerous actions ask for confirmation in a pop-up.
- Revenue on the dashboard = total of all orders that are not cancelled.
TXT;
    }
}
