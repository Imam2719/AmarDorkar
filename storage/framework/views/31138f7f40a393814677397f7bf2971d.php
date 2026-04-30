


<div class="info-panel" id="panelAbout">
<div style="position:absolute;inset:0;" onclick="closePanel('panelAbout')"></div>
<div class="info-sheet">
    <div class="info-sheet-handle"></div>
    <div class="info-sheet-header">
        <span class="info-sheet-title"><?php echo e(__('messages.about_us')); ?></span>
        <button class="info-sheet-close" onclick="closePanel('panelAbout')">×</button>
    </div>
    <div class="info-sheet-body bengali-text">
        <?php if(app()->getLocale()==='bn'): ?>
        <h4>আমারদরকার সম্পর্কে</h4>
        <p>আমারদরকার বাংলাদেশের একটি বিশ্বস্ত অনলাইন শপিং প্ল্যাটফর্ম। আমরা ২০২২ সালে যাত্রা শুরু করেছি এবং গ্রাহকদের কাছে সেরা মানের পণ্য সুলভ মূল্যে পৌঁছে দেওয়াই আমাদের লক্ষ্য।</p>
        <h4>কেন আমাদের বেছে নেবেন</h4>
        <ul>
            <li>সকল ক্যাটাগরিতে বিশাল পণ্য সংগ্রহ</li>
            <li>ঢাকায় ১–৩ দিনে হোম ডেলিভারি</li>
            <li>পণ্য পেয়ে পেমেন্ট (ক্যাশ অন ডেলিভারি)</li>
            <li>৭ দিনের সহজ রিটার্ন পলিসি</li>
            <li>১০০% অরিজিনাল পণ্যের নিশ্চয়তা</li>
        </ul>
        <?php else: ?>
        <h4>About amardorkar</h4>
        <p>amardorkar is a trusted online shopping platform in Bangladesh, founded in 2022. Our mission is to deliver quality products at affordable prices right to your doorstep.</p>
        <h4>Why Choose Us</h4>
        <ul>
            <li>Huge product collection across all categories</li>
            <li>Home delivery in Dhaka within 1–3 days</li>
            <li>Cash on delivery — pay when you receive</li>
            <li>7-day easy return policy</li>
            <li>100% authentic products guaranteed</li>
        </ul>
        <?php endif; ?>
    </div>
</div>
</div>


<div class="info-panel" id="panelPrivacy">
<div style="position:absolute;inset:0;" onclick="closePanel('panelPrivacy')"></div>
<div class="info-sheet">
    <div class="info-sheet-handle"></div>
    <div class="info-sheet-header">
        <span class="info-sheet-title"><?php echo e(__('messages.privacy_policy')); ?></span>
        <button class="info-sheet-close" onclick="closePanel('panelPrivacy')">×</button>
    </div>
    <div class="info-sheet-body bengali-text">
        <?php if(app()->getLocale()==='bn'): ?>
        <h4>তথ্য সংগ্রহ</h4>
        <p>আমরা শুধুমাত্র অর্ডার প্রসেস করার জন্য প্রয়োজনীয় তথ্য সংগ্রহ করি: নাম, মোবাইল, ডেলিভারি ঠিকানা এবং ইমেইল।</p>
        <h4>তথ্য সুরক্ষা ও শেয়ার</h4>
        <ul>
            <li>SSL এনক্রিপশন দিয়ে সুরক্ষিত</li>
            <li>তৃতীয় পক্ষের সাথে তথ্য বিক্রি বা শেয়ার করা হয় না</li>
            <li>শুধু ডেলিভারির জন্য লজিস্টিক পার্টনারের সাথে শেয়ার হয়</li>
        </ul>
        <h4>কুকি নীতি</h4>
        <p>আমরা সাইটের অভিজ্ঞতা উন্নত করতে কুকি ব্যবহার করি। ব্রাউজার সেটিংস থেকে যেকোনো সময় বন্ধ করা যাবে।</p>
        <?php else: ?>
        <h4>Data Collection</h4>
        <p>We only collect necessary information: name, phone, delivery address, and email.</p>
        <h4>Data Security & Sharing</h4>
        <ul>
            <li>Protected with SSL encryption</li>
            <li>Never sold or shared with third parties</li>
            <li>Delivery info shared only with logistics partners</li>
        </ul>
        <h4>Cookie Policy</h4>
        <p>We use cookies to improve browsing experience. You can disable them in browser settings anytime.</p>
        <?php endif; ?>
    </div>
</div>
</div>


<div class="info-panel" id="panelContact">
<div style="position:absolute;inset:0;" onclick="closePanel('panelContact')"></div>
<div class="info-sheet">
    <div class="info-sheet-handle"></div>
    <div class="info-sheet-header">
        <span class="info-sheet-title"><?php echo e(__('messages.contact_us')); ?></span>
        <button class="info-sheet-close" onclick="closePanel('panelContact')">×</button>
    </div>
    <div class="info-sheet-body" style="padding:0;">
        <iframe
            src="https://maps.google.com/maps?q=23.0061,91.3970&z=14&output=embed"
            width="100%" height="340"
            style="border:0;display:block;"
            allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
        <div style="padding:16px;display:flex;flex-direction:column;gap:10px;">
            <div style="display:flex;align-items:center;gap:10px;font-size:13.5px;">
                <i class="fas fa-map-marker-alt text-primary" style="width:16px;"></i>
                <span class="bengali-text"><?php echo e(app()->getLocale()==='bn' ? 'ফেনী সদর, ফেনী, বাংলাদেশ' : 'Feni Sadar, Feni, Bangladesh'); ?></span>
            </div>
            <div style="display:flex;align-items:center;gap:10px;font-size:13.5px;">
                <i class="fas fa-phone text-primary" style="width:16px;"></i>
                <a href="tel:+8801863719755" class="text-decoration-none text-dark">01863-719755</a>
            </div>
            <div style="display:flex;align-items:center;gap:10px;font-size:13.5px;">
                <i class="fas fa-envelope text-primary" style="width:16px;"></i>
                <a href="mailto:alimamuddin2710@gmail.com" class="text-decoration-none text-dark">alimamuddin2710@gmail.com</a>
            </div>
            <a href="https://maps.app.goo.gl/CnYXtZ14LfRQy4DK8"
               target="_blank" rel="noopener"
               style="display:flex;align-items:center;justify-content:center;gap:8px;padding:11px 16px;background:#1a9e4a;color:#fff;border-radius:9px;text-decoration:none;font-size:13.5px;font-weight:600;">
                <i class="fas fa-directions"></i>
                <?php echo e(app()->getLocale()==='bn' ? 'গুগল ম্যাপে দেখুন' : 'Open in Google Maps'); ?>

            </a>
        </div>
    </div>
</div>
</div>


<div class="info-panel" id="panelHelp">
<div style="position:absolute;inset:0;" onclick="closePanel('panelHelp')"></div>
<div class="info-sheet">
    <div class="info-sheet-handle"></div>
    <div class="info-sheet-header">
        <span class="info-sheet-title"><?php echo e(__('messages.help_center')); ?></span>
        <button class="info-sheet-close" onclick="closePanel('panelHelp')">×</button>
    </div>
    <div class="info-sheet-body bengali-text">
        <?php if(app()->getLocale()==='bn'): ?>
        <h4>কিভাবে অর্ডার করবেন</h4>
        <ol>
            <li>পণ্যে <strong>"কার্টে যোগ করুন"</strong> ক্লিক করুন</li>
            <li>কার্ট আইকন থেকে <strong>"চেকআউট"</strong> করুন</li>
            <li>নাম, মোবাইল ও ঠিকানা দিয়ে অর্ডার কনফার্ম করুন</li>
        </ol>
        <h4>সচরাচর জিজ্ঞাসা</h4>
        <p><strong>ডেলিভারি কতদিনে?</strong><br>ঢাকায় ১–৩ কার্যদিবস, বাইরে ৩–৫ কার্যদিবস।</p>
        <p><strong>অ্যাকাউন্ট ছাড়া অর্ডার?</strong><br>হ্যাঁ, গেস্ট হিসেবে অর্ডার দেওয়া যাবে।</p>
        <p><strong>সমস্যায় যোগাযোগ:</strong><br>📞 01863-719755 | ✉️ alimamuddin2710@gmail.com</p>
        <?php else: ?>
        <h4>How to Place an Order</h4>
        <ol>
            <li>Click <strong>"Add to Cart"</strong> on your product</li>
            <li>Go to cart and click <strong>"Checkout"</strong></li>
            <li>Enter your details and confirm the order</li>
        </ol>
        <h4>FAQ</h4>
        <p><strong>Delivery time?</strong><br>Dhaka: 1–3 days, Outside Dhaka: 3–5 days.</p>
        <p><strong>Order without account?</strong><br>Yes, guest checkout is available.</p>
        <p><strong>Need help?</strong><br>📞 01863-719755 | ✉️ alimamuddin2710@gmail.com</p>
        <?php endif; ?>
    </div>
</div>
</div>


<div class="info-panel" id="panelReturns">
<div style="position:absolute;inset:0;" onclick="closePanel('panelReturns')"></div>
<div class="info-sheet">
    <div class="info-sheet-handle"></div>
    <div class="info-sheet-header">
        <span class="info-sheet-title"><?php echo e(__('messages.returns_refunds')); ?></span>
        <button class="info-sheet-close" onclick="closePanel('panelReturns')">×</button>
    </div>
    <div class="info-sheet-body bengali-text">
        <?php if(app()->getLocale()==='bn'): ?>
        <span class="info-badge">৭ দিনের রিটার্ন গ্যারান্টি</span>
        <h4 class="mt-3">রিটার্ন যোগ্য পরিস্থিতি</h4>
        <ul>
            <li>ক্ষতিগ্রস্ত বা ত্রুটিপূর্ণ পণ্য পেলে</li>
            <li>ভুল পণ্য ডেলিভারি হলে</li>
            <li>পণ্য বর্ণনার সাথে মিল না থাকলে</li>
        </ul>
        <h4>রিটার্নের শর্ত</h4>
        <ul>
            <li>অব্যবহৃত ও মূল প্যাকেজিং সহ থাকতে হবে</li>
            <li>রিটার্নের আগে যোগাযোগ করুন: <strong>01863-719755</strong></li>
        </ul>
        <h4>রিফান্ড</h4>
        <ul>
            <li><strong>bKash / Nagad</strong> — ২৪ ঘন্টার মধ্যে</li>
            <li><strong>ব্যাংক ট্রান্সফার</strong> — ৩–৫ কার্যদিবস</li>
        </ul>
        <?php else: ?>
        <span class="info-badge">7-Day Return Guarantee</span>
        <h4 class="mt-3">Eligible Return Situations</h4>
        <ul>
            <li>Damaged or defective product received</li>
            <li>Wrong product delivered</li>
            <li>Product doesn't match description</li>
        </ul>
        <h4>Return Conditions</h4>
        <ul>
            <li>Must be unused in original packaging</li>
            <li>Contact us first: <strong>01863-719755</strong></li>
        </ul>
        <h4>Refund Timeline</h4>
        <ul>
            <li><strong>bKash / Nagad</strong> — within 24 hours</li>
            <li><strong>Bank transfer</strong> — 3–5 business days</li>
        </ul>
        <?php endif; ?>
    </div>
</div>
</div>


<div class="info-panel" id="panelPayments">
<div style="position:absolute;inset:0;" onclick="closePanel('panelPayments')"></div>
<div class="info-sheet">
    <div class="info-sheet-handle"></div>
    <div class="info-sheet-header">
        <span class="info-sheet-title"><?php echo e(__('messages.payments')); ?></span>
        <button class="info-sheet-close" onclick="closePanel('panelPayments')">×</button>
    </div>
    <div class="info-sheet-body bengali-text">
        <?php if(app()->getLocale()==='bn'): ?>
        <h4>ক্যাশ অন ডেলিভারি</h4>
        <span class="info-badge">সবচেয়ে জনপ্রিয়</span>
        <p class="mt-2">পণ্য হাতে পেয়ে নগদে পেমেন্ট — কোনো অগ্রিম পেমেন্ট নেই।</p>
        <h4>মোবাইল ব্যাংকিং</h4>
        <div class="info-company-card">
            <h5>bKash / Nagad</h5>
            <p>নম্বর: <strong>01863-719755</strong><br>Send Money → নম্বর লিখুন → Reference-এ অর্ডার নম্বর দিন</p>
        </div>
        <p style="font-size:12px;color:#94a3b8;" class="mt-2">⚠️ আমারদরকার কখনো ফোনে পেমেন্ট তথ্য চায় না।</p>
        <?php else: ?>
        <h4>Cash on Delivery</h4>
        <span class="info-badge">Most Popular</span>
        <p class="mt-2">Pay in cash on delivery — no upfront payment required.</p>
        <h4>Mobile Banking</h4>
        <div class="info-company-card">
            <h5>bKash / Nagad</h5>
            <p>Number: <strong>01863-719755</strong><br>Send Money → enter number → add Order Number as reference</p>
        </div>
        <p style="font-size:12px;color:#94a3b8;" class="mt-2">⚠️ amardorkar will never ask for payment info via phone.</p>
        <?php endif; ?>
    </div>
</div>
</div>


<div class="info-panel" id="panelShipping">
<div style="position:absolute;inset:0;" onclick="closePanel('panelShipping')"></div>
<div class="info-sheet">
    <div class="info-sheet-handle"></div>
    <div class="info-sheet-header">
        <span class="info-sheet-title"><?php echo e(__('messages.shipping')); ?></span>
        <button class="info-sheet-close" onclick="closePanel('panelShipping')">×</button>
    </div>
    <div class="info-sheet-body bengali-text">
        <?php if(app()->getLocale()==='bn'): ?>
        <h4>ঢাকার মধ্যে ডেলিভারি</h4>
        <span class="info-badge">৳৬০ — ১–৩ কার্যদিবস</span>
        <p class="mt-2">ঢাকার যেকোনো এলাকায় মাত্র ৳৬০ শিপিং চার্জে। <strong>১,০০০ টাকার বেশি অর্ডারে ফ্রি ডেলিভারি!</strong></p>
        <h4>ঢাকার বাইরে ডেলিভারি</h4>
        <span class="info-badge">৳১২০ — ৩–৫ কার্যদিবস</span>
        <p class="mt-2">বাংলাদেশের যেকোনো জেলায় কুরিয়ার সার্ভিসে।</p>
        <h4>ট্র্যাকিং</h4>
        <p>অর্ডার শিপ হলে SMS পাবেন। "আমার অর্ডার" থেকেও দেখুন।</p>
        <?php else: ?>
        <h4>Delivery Inside Dhaka</h4>
        <span class="info-badge">৳60 — 1–3 Business Days</span>
        <p class="mt-2">Anywhere in Dhaka for ৳60. <strong>Free delivery on orders over ৳1,000!</strong></p>
        <h4>Delivery Outside Dhaka</h4>
        <span class="info-badge">৳120 — 3–5 Business Days</span>
        <p class="mt-2">Courier delivery to any district in Bangladesh.</p>
        <h4>Tracking</h4>
        <p>You'll receive an SMS when shipped. Track via "My Orders" anytime.</p>
        <?php endif; ?>
    </div>
</div>
</div>


<div class="info-panel" id="panelPartner">
<div style="position:absolute;inset:0;" onclick="closePanel('panelPartner')"></div>
<div class="info-sheet">
    <div class="info-sheet-handle"></div>
    <div class="info-sheet-header">
        <span class="info-sheet-title"><?php echo e(__('messages.business_partner')); ?></span>
        <button class="info-sheet-close" onclick="closePanel('panelPartner')">×</button>
    </div>
    <div class="info-sheet-body bengali-text">
        <?php if(app()->getLocale()==='bn'): ?>
        <h4>আমারদরকার পার্টনার প্রোগ্রাম</h4>
        <span class="info-badge">রেজিস্ট্রেশন বিনামূল্যে</span>
        <p class="mt-2">ঘরে বসে আয় করুন — কোনো বিনিয়োগ ছাড়াই।</p>
        <h4>সুবিধাসমূহ</h4>
        <ul>
            <li>প্রতি বিক্রিতে <strong>৫–১৫% কমিশন</strong></li>
            <li>মাসিক পেমেন্ট bKash/Nagad-এ</li>
            <li>বিশেষ পার্টনার মূল্যে পণ্য</li>
        </ul>
        <h4>যোগ দিতে</h4>
        <p>ইমেইল করুন: <a href="mailto:alimamuddin2710@gmail.com" class="text-success">alimamuddin2710@gmail.com</a></p>
        <?php else: ?>
        <h4>amardorkar Partner Program</h4>
        <span class="info-badge">Free Registration</span>
        <p class="mt-2">Earn from home — no investment needed.</p>
        <h4>Benefits</h4>
        <ul>
            <li><strong>5–15% commission</strong> per sale</li>
            <li>Monthly payouts via bKash/Nagad</li>
            <li>Exclusive partner product pricing</li>
        </ul>
        <h4>Join Now</h4>
        <p>Email us: <a href="mailto:alimamuddin2710@gmail.com" class="text-success">alimamuddin2710@gmail.com</a></p>
        <?php endif; ?>
    </div>
</div>
</div>


<div class="info-panel" id="panelCareers">
<div style="position:absolute;inset:0;" onclick="closePanel('panelCareers')"></div>
<div class="info-sheet">
    <div class="info-sheet-handle"></div>
    <div class="info-sheet-header">
        <span class="info-sheet-title"><?php echo e(__('messages.careers')); ?></span>
        <button class="info-sheet-close" onclick="closePanel('panelCareers')">×</button>
    </div>
    <div class="info-sheet-body bengali-text">
        <?php if(app()->getLocale()==='bn'): ?>
        <h4>আমাদের সাথে কাজ করুন</h4>
        <div class="info-company-card">
            <h5>ডেলিভারি রাইডার</h5>
            <p>নিজস্ব বাইক থাকলে অগ্রাধিকার। আকর্ষণীয় কমিশন।</p>
        </div>
        <div class="info-company-card">
            <h5>কাস্টমার সার্ভিস</h5>
            <p>বাংলায় যোগাযোগে দক্ষ। ফুল-টাইম ও পার্ট-টাইম।</p>
        </div>
        <div class="info-company-card">
            <h5>ডিজিটাল মার্কেটার</h5>
            <p>Facebook Ads / Social Media অভিজ্ঞতা সম্পন্ন।</p>
        </div>
        <h4>আবেদন</h4>
        <p>CV পাঠান: <a href="mailto:alimamuddin2710@gmail.com" class="text-success">alimamuddin2710@gmail.com</a></p>
        <?php else: ?>
        <h4>Work With Us</h4>
        <div class="info-company-card">
            <h5>Delivery Rider</h5>
            <p>Own motorcycle preferred. Attractive commission.</p>
        </div>
        <div class="info-company-card">
            <h5>Customer Service</h5>
            <p>Bengali skills required. Full-time & part-time available.</p>
        </div>
        <div class="info-company-card">
            <h5>Digital Marketer</h5>
            <p>Facebook Ads / Social Media experience preferred.</p>
        </div>
        <h4>Apply</h4>
        <p>Send CV to: <a href="mailto:alimamuddin2710@gmail.com" class="text-success">alimamuddin2710@gmail.com</a></p>
        <?php endif; ?>
    </div>
</div>
</div>
<?php /**PATH E:\amardorkar-laravel\resources\views/layouts/partials/info-panels.blade.php ENDPATH**/ ?>