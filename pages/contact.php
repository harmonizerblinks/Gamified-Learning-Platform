
<?php 

require_once '../includes/header.php';
require_once '../includes/navbar.php';
$page_title = "Contact Us - " . SITE_NAME;

?>

<!-- Hero Section -->
<section class="bg-purple text-white py-5">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Get In Touch</h1>
        <p class="lead">We'd love to hear from you!</p>
    </div>
</section>

<!-- Contact Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="mb-4">Send Us a Message</h3>
                        
                        <?php display_messages(); ?>
                        
                        <form action="/actions/contact-process.php" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control form-control-lg" id="name" name="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                                </div>
                                <div class="col-12">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <input type="text" class="form-control form-control-lg" id="subject" name="subject" required>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">Send Message</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="mb-4">Contact Information</h4>
                        
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div class="icon-box bg-purple-light text-purple rounded-circle p-3">
                                    <i class="fas fa-envelope fa-lg"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Email</h6>
                                <p class="text-muted mb-0">support@learnhub.com</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div class="icon-box bg-purple-light text-purple rounded-circle p-3">
                                    <i class="fas fa-phone fa-lg"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Phone</h6>
                                <p class="text-muted mb-0">+233 XX XXX XXXX</p>
                            </div>
                        </div>
                        
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="icon-box bg-purple-light text-purple rounded-circle p-3">
                                    <i class="fas fa-map-marker-alt fa-lg"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Location</h6>
                                <p class="text-muted mb-0">Accra, Ghana</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="mb-4">Office Hours</h4>
                        <p class="mb-2"><strong>Monday - Friday:</strong> 9:00 AM - 6:00 PM</p>
                        <p class="mb-2"><strong>Saturday:</strong> 10:00 AM - 4:00 PM</p>
                        <p class="mb-0"><strong>Sunday:</strong> Closed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
