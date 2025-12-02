<?php
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>greytHR Middle East Pricing - Complete HR & Payroll Software</title>
    <style>
        /* Base Styles */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Helvetica Neue", Arial, sans-serif; line-height: 1.6; color: #333; }
        
        /* Header Styles */
        .header { 
            background: #fff; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-links a {
            color: #2d3748;
            text-decoration: none;
            margin: 0 1rem;
            font-weight: 500;
        }
        
        .cta-buttons button {
            padding: 0.75rem 1.5rem;
            margin-left: 1rem;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .free-trial {
            background: #2563eb;
            color: white;
            border: none;
        }
        
        /* Pricing Hero */
        .pricing-hero {
            text-align: center;
            padding: 4rem 2rem;
            background: #f8fafc;
        }
        
        .pricing-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #1e293b;
        }
        
        /* Pricing Table */
        .pricing-table {
            width: 100%;
            max-width: 1200px;
            margin: 2rem auto;
            border-collapse: collapse;
        }
        
        .pricing-table th, 
        .pricing-table td {
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        
        .plan-header {
            background: #f1f5f9;
            font-weight: 600;
        }
        
        .price-tag {
            font-size: 2rem;
            color: #2563eb;
            margin: 1rem 0;
        }
        
        .feature-table {
            width: 100%;
            margin: 2rem 0;
        }
        
        .feature-category {
            background: #f8fafc;
            font-weight: 600;
            padding: 1rem;
        }
        
        .feature-row td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        /* Symbol Styling */
        .check { color: #10b981; font-weight: bold; }
        .cross { color: #ef4444; }
        .limited { color: #f59e0b; }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header { flex-direction: column; text-align: center; }
            .nav-links { margin: 1rem 0; }
            .cta-buttons { margin-top: 1rem; }
            
            .pricing-table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="header">
        <div class="brand">
            <img src="https://www.greythr.com/static/images/logo.svg" alt="greytHR Logo" width="120">
        </div>
        <nav class="nav-links">
            <a href="#product">Product</a>
            <a href="#pricing">Pricing</a>
            <a href="#resources">Resources</a>
        </nav>
        <div class="cta-buttons">
            <button class="request-demo">Request Demo</button>
            <button class="free-trial">Start Free Trial</button>
        </div>
    </header>

    <!-- Pricing Hero Section -->
    <section class="pricing-hero">
        <h1>Simple, Transparent Pricing for Middle East Businesses</h1>
        <p>Start with 10 employees FREE. Pay only when you grow.</p>
    </section>

    <!-- Pricing Plans Table -->
    <table class="pricing-table">
        <thead>
            <tr>
                <th></th>
                <th class="plan-header">
                    <h3>Starter</h3>
                    <div class="price-tag">FREE</div>
                    <p>Up to 10 Employees</p>
                    <button class="free-trial">Start Free</button>
                </th>
                <th class="plan-header">
                    <h3>Essential</h3>
                    <div class="price-tag">$50</div>
                    <p>+ $2/additional employee</p>
                    <button class="free-trial">Choose Plan</button>
                </th>
                <th class="plan-header">
                    <h3>Growth</h3>
                    <div class="price-tag">$60</div>
                    <p>+ $3/additional employee</p>
                    <button class="free-trial">Choose Plan</button>
                </th>
                <th class="plan-header">
                    <h3>Enterprise</h3>
                    <div class="price-tag">$80</div>
                    <p>+ $5/additional employee</p>
                    <button class="free-trial">Contact Sales</button>
                </th>
            </tr>
        </thead>
        
        <!-- Features Sections -->
        <tbody>
            <!-- Core HR -->
            <tr class="feature-category">
                <td colspan="5">Core HR Features</td>
            </tr>
            <tr class="feature-row">
                <td>Employee Database</td>
                <td class="check">✓</td>
                <td class="check">✓</td>
                <td class="check">✓</td>
                <td class="check">✓</td>
            </tr>
            
            <!-- Payroll Features -->
            <tr class="feature-category">
                <td colspan="5">Payroll Management</td>
            </tr>
            <tr class="feature-row">
                <td>Automated WPS Compliance</td>
                <td class="check">✓</td>
                <td class="check">✓</td>
                <td class="check">✓</td>
                <td class="check">✓</td>
            </tr>
            
            <!-- Add more features following the PDF structure -->
            
        </tbody>
    </table>

    <!-- FAQ Section -->
    <section class="faq-section" style="max-width: 800px; margin: 3rem auto; padding: 2rem;">
        <h2 style="margin-bottom: 2rem;">Frequently Asked Questions</h2>
        <div class="faq-item">
            <h3>What happens after my free trial ends?</h3>
            <p>You can choose to upgrade to any paid plan or continue with the free Starter plan with limited features.</p>
        </div>
        <!-- Add more FAQ items -->
    </section>

    <!-- Footer -->
    <footer style="background: #1e293b; color: white; padding: 3rem 2rem;">
        <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem;">
            <!-- Footer columns -->
        </div>
        <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #334155;">
            <p>© 2025 Greytip Software Pvt. Ltd. All rights reserved</p>
        </div>
    </footer>

</body>
</html>';
?>