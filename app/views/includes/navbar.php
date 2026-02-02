<?php  
require_once __DIR__ . '/../../../config/dbcon.php'; 
require_once __DIR__ . '/../../../config/constants.php';  
?>  


<link rel="stylesheet" href="css/navbar.css">  

<!-- Header & Navigation --> 
<nav class="navbar">     
    <div class="nav-container" data-aos="fade-zoom-in" data-aos-easing="ease-in-back" data-aos-delay="100" data-aos-offset="0">         
        <a href="<?php echo BASE_URL; ?>/public/index.php" class="logo">             
            EMPIRE.         
        </a>                  
        
        <ul class="nav-menu" id="nav-menu">             
            <li><a href="#home" class="nav-link">Home</a></li>             
            <li><a href="#story" class="nav-link">Our Story</a></li>             
            <li><a href="#arrivals" class="nav-link">New Arrivals</a></li>             
            <li><a href="#team" class="nav-link">Team</a></li>             
            <li><a href="#contact" class="nav-link">Contact</a></li>                          
            
            <!-- Search Bar -->             
            <div class="search-container">                 
                <input type="text" class="search-bar" placeholder="Search products...">                 
                <button class="search-btn">                     
                    <i class="fas fa-search"></i>                 
                </button>             
            </div>         
        </ul>                  
        
        <!-- Icons Section -->         
        <div class="nav-icons">             
            <!-- Shopping Cart -->             
            <button class="icon-btn cart-btn">                 
                <i class="fas fa-shopping-cart"></i>             
            </button>                          
            
            <!-- User Icon - Link to login page -->
            <a href="<?php echo BASE_URL; ?>/app/views/auth/login.php" class="icon-btn user-btn">     
                <i class="fas fa-user"></i> 
            </a>         
        </div>                  
        
        <div class="mobile-toggle" id="mobile-toggle">             
            <span></span>             
            <span></span>             
            <span></span>         
        </div>     
    </div> 
</nav>

