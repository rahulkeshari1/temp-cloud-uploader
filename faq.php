<?php

?>

<style>
    .faq-container {
        max-width: 800px;
        margin: 1.5rem auto 1rem auto;
        padding: 0 1rem;
    }
    
    .faq-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .faq-header h1 {
        font-size: 1.5rem;
        color: #4361ee;
        margin-bottom: 0.5rem;
    }
    
    .faq-header p {
        color: #6c757d;
        font-size: 1rem;
    }
    
    .faq-accordion {
        background-color: #1e1e1e;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .faq-item {
        border-bottom: 1px solid #2e2e2e;
    }
    
    .faq-item:last-child {
        border-bottom: none;
    }
    
    .faq-question {
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        background-color: #252525;
        transition: background-color 0.3s;
    }
    
    .faq-question:hover {
        background-color: #2a2a2a;
    }
    
    .faq-question h3 {
        margin: 0;
        font-size: 1.1rem;
        color: #f1f1f1;
        font-weight: 500;
    }
    
    .faq-question .icon {
        font-size: 1.2rem;
        color: #4361ee;
        transition: transform 0.3s;
    }
    
    .faq-answer {
        padding: 0 1.5rem;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out, padding 0.3s ease;
    }
    
    .faq-answer-inner {
        padding: 0 0 1.5rem;
    }
    
    .faq-answer p {
        margin-top: 1rem;
        color: #cccccc;
        line-height: 1.6;
        /* margin-bottom: 1rem; */
    }
    
    .faq-answer p:last-child {
        margin-bottom: 0;
    }
    
    .faq-item.active .faq-question {
        background-color: #2a2a2a;
    }
    
    .faq-item.active .faq-question .icon {
        transform: rotate(45deg);
    }
    
    .faq-item.active .faq-answer {
        max-height: 500px;
        padding: 0 1.5rem;
    }
    
    .contact-support {
        margin-top: 3rem;
        text-align: center;
        padding: 2rem;
        background-color: #252525;
        border-radius: 8px;
    }
    
    .contact-support h2 {
        color: #4361ee;
        margin-bottom: 1rem;
    }
    
    .contact-support p {
        color: #cccccc;
        margin-bottom: 1.5rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .contact-btn {
        display: inline-block;
        padding: 0.8rem 1.8rem;
        background-color: #4361ee;
        color: white;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.3s;
    }
    
    .contact-btn:hover {
        background-color: #3a56d4;
    }
    
    @media (max-width: 768px) {
        .faq-header h1 {
            font-size: 1.5rem;
        }
        
        .faq-question {
            padding: 1.2rem;
        }
        
        .faq-question h3 {
            font-size: 1rem;
        }
    }
</style>

<div class="faq-container">
    <div class="faq-header">
    <h1><span class="fa fa-question-circle" aria-hidden="true"></span> <span>FAQs</span></h1>  
          <p>Find answers to common questions about our file upload service</p>
    </div>
    
    <div class="faq-accordion" id="faqAccordion">
        <!-- Question 1 -->
        <div class="faq-item">
            <div class="faq-question">
                <h3>What types of files can I upload?</h3>
                <div class="icon"><i class="fas fa-plus"></i></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>You can upload most common file types including documents (PDF, DOC, DOCX), images (JPG, PNG, GIF), archives (ZIP, RAR), and more. The maximum file size is 20MB per file.</p>
                </div>
            </div>
        </div>
        
        <!-- Question 2 -->
        <div class="faq-item">
            <div class="faq-question">
                <h3>How long are my files stored?</h3>
                <div class="icon"><i class="fas fa-plus"></i></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>All uploaded files are automatically deleted after 24 hours from the time of upload. This is a security measure to protect your data.</p>
                    <p>You can see the exact expiration time on the download page after uploading your file.</p>
                </div>
            </div>
        </div>
        
        <!-- Question 3 -->
        <div class="faq-item">
            <div class="faq-question">
                <h3>Is there a way to password-protect my files?</h3>
                <div class="icon"><i class="fas fa-plus"></i></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Yes! When uploading a file, simply check the "Password protect" option and set a password. The recipient will need this password to download the file.</p>
                    <p>Please note that we cannot recover passwords if they're forgotten, so be sure to share the password securely with your intended recipient.</p>
                </div>
            </div>
        </div>
        
        <!-- Question 4 -->
        <div class="faq-item">
            <div class="faq-question">
                <h3>How do I share my uploaded files?</h3>
                <div class="icon"><i class="fas fa-plus"></i></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>After uploading, you'll receive a unique download link. You can share this link with anyone you want to access the file.</p>
                    <p>For password-protected files, remember to share the password separately through a secure channel.</p>
                    <p>The "Copy Link" button on the upload success page makes it easy to copy the download URL to your clipboard.</p>
                </div>
            </div>
        </div>
        
        <!-- Question 5 -->
        <div class="faq-item">
            <div class="faq-question">
                <h3>Is my data secure and private?</h3>
                <div class="icon"><i class="fas fa-plus"></i></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>We take privacy and security seriously. All files are stored encrypted on our servers and are automatically deleted after 24 hours.</p>
                    <p>Without the exact download link (which contains a unique token), no one can access your files. For additional security, we recommend using the password protection feature.</p>
                    <p>We do not scan or inspect the contents of your files beyond basic virus scanning for malicious content.</p>
                </div>
            </div>
        </div>
        
        <!-- Question 6 -->
        <div class="faq-item">
            <div class="faq-question">
                <h3>What happens if my file expires?</h3>
                <div class="icon"><i class="fas fa-plus"></i></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Once a file expires (after 24 hours), it is permanently deleted from our servers and cannot be recovered.</p>
                    <p>The download link will show an "expired" message and the file will no longer be available for download.</p>
                    <p>If you need the file again, you'll need to upload it once more.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- <div class="contact-support">
        <h2><i class="fas fa-headset"></i> Still have questions?</h2>
        <p>Can't find what you're looking for? Our support team is here to help you with any questions or issues you might have.</p>
        <a href="contact.php" class="contact-btn"><i class="fas fa-envelope"></i> Contact Support</a>
    </div> -->
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const faqItems = document.querySelectorAll('.faq-item');
        
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            
            question.addEventListener('click', () => {
                // Close all other items
                faqItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
                
                // Toggle current item
                item.classList.toggle('active');
            });
        });
        
        // Open first item by default
        if (faqItems.length > 0) {
            faqItems[0].classList.add('active');
        }
    });
</script>

