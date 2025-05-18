
<?php
include 'connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E- Healthcare Workflow platform </title>

    <script src="https://kit.fontawesome.com/c1df782baf.js"></script>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.1.0/uicons-thin-rounded/css/uicons-thin-rounded.css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-nLjIyyX1OPRiVg6vOOrAjMxN7D3+IBx5G8b5Oxu7RStTNoiAqgJc3JodO1K2jQ2tMlRnQqE0XRFrf6I1eWz02w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" />
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.1.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <link rel="stylesheet" href="style.css">

</head>


<body> 

    <header>
        
        <div class="logo"><img src="images/logo3.png" alt=""></div>

        <nav class="navbar">
            <a href="#Home">Home</a>
            <a href="#About">About</a>
            <a href="#doctors">Our Doctor's</a>
            <a href="#news">News</a>
            <a href="#services">Services</a>
        </nav>

        <div class="right-icons">
            <div id="menu-bars" class="fas fa-bars"></div>
            <a href="login.php" class="btn">Login</a>
        </div>
        

    </header>

    <!-- header section ended -->

    <!-- Home section started -->

    <a name="Home"></a>
        <div class="main-home">

            <div class="home">
                <div class="home-left-content">
                    <span>welcome to E- Healthcare Workflow platform</span>
                    <h2>We take care our<br> Patients Healths</h2>
                    <p class="lorem">"An efficient, secure, and user-friendly platform streamlining healthcare workflows seamlessly."</p>
                
                        <div class="home-btn">
                            <a href="appointmentdoctor.php">Apply as Doctor</a>
                            <a class="homebtnsec" href="login.php">login</a>
                        </div>
                        
                    </div>

                <div class="home-right-content">
                    <img src="images/hero2.png" alt="">
                </div>
            </div>
        </div>


        <div class="technology">
            <div class="main-technology">
                
                <div class="inner-technology">
                    <span></span>
                    <i class="fa-solid fa-user-tie"></i>
                    <h2>Admin</h2>
                    <p>The admin ensures compliance with medical data security and privacy regulations.</p>
                </div>

                <div class="inner-technology">
                    <span></span>
                    <i class="fa-solid fa-person"></i>
                    <h2>Pateint's</h2>
                    <p>The system allows patients to view their medical history and prescriptions.They can access doctors’ availability and choose preferred specialists</p>
                </div>

                <div class="inner-technology">
                    <span></span>
                    <i class="fas fa-user-md"></i>
                    <h2>Doctor's</h2>
                    <p>A doctor can log in to access their dashboard and schedule.The system ensures secure handling of patient data and privacy.</p>
                </div>
            </div>
        </div>

    <!-- home section ends -->

    <!-- About us section started -->
<a name="About"></a>
        <div class="main-about">

            <div class="about-heading">About Us</div>

            <div class="inner-main-about">
                <div class="about-inner-content-left">
                    <img src="images/about1.png" alt="">
                </div>

                <div class="about-inner-content">
                    <div class="about-right-content">
                        <h2>Elevating Healthcare Through Innovation and Compassion.</h2>
                        <p>We provide the most full medical services, so every person could have the pportunity 
                         o receive qualitative medical help.</p>
                        <p class="aboutsec-content">
                        At E-Health Care, we provide comprehensive medical services, ensuring that every individual has access to high-quality healthcare.

Our clinic has grown into a world-class facility specializing in advanced treatments, including tooth loss solutions, dental cosmetics, and restorative dentistry. With over 30 years of expertise, we are among the most qualified implant providers in Australia, delivering excellence through experience and innovation.
                            </p>
                            <button class="aboutbtn">Read More</button>
                    </div>
                </div>
            </div>
        </div>

    <!-- About us section ends -->

    <!-- our doctors -->
<a name="doctors"></a>
        <div class="main-doctors">
            <div class="doctors-heading">
                <h2>Our Doctors</h2>
            </div>

            <div class="main-inner-doctor">
                <div class="doc-poster">
                   
                    <img src="images/team1.jpg" alt="">

                    <div class="doc-details">
                        <h2>Dr Priya sharma</h2>
                        
                        <i class="fab fa-linkedin-in"></i>
                        <i class="fab fa-instagram"></i>
                    </div>

                </div>

                <div class="doc-poster">
                    
                    <img src="images/team2.jpg" alt="">
                    <div class="doc-details">
                        <h2>Dr Raj sasuke</h2>
                        
                        <i class="fab fa-linkedin-in"></i>
                        <i class="fab fa-instagram"></i>
                    </div>
                </div>

                <div class="doc-poster">
                   
                    <img src="images/team3.jpg" alt="">
                    <div class="doc-details">
                        <h2>Dr Nita Kaushik</h2>
                        
                        <i class="fab fa-linkedin-in"></i>
                        <i class="fab fa-instagram"></i>
                    </div>
                </div>

               
                
              
            </div>

        </div>

    <!-- our doctors ended -->

    
   
<!-- News and Notice -->
<a name="news"></a>
<section style="margin: 30px 50px; padding: 20px; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-family: sans-serif;">
  <h1 style="text-align: center; color: #007bff; font-size: 40px; margin-bottom: 40px;">News & Notice</h1>

  <div style="display: flex; justify-content: center; gap: 30px; height: 400px;">

    <!-- News Section -->
    <div style="flex: 1; background: #fff; border: 1px solid #ddd; border-radius: 10px; padding: 20px; overflow: hidden; display: flex; flex-direction: column;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h2 style="font-size: 28px; color: #002b5c; border-bottom: 2px solid #002b5c; padding-bottom: 5px;">News & Events</h2>
      </div>
      <div style="flex: 1; overflow: hidden; position: relative; font-size: 18px;">
        <marquee direction="up" scrollamount="6" style="height: 100%;">
          <?php
          $news = $conn->query("SELECT title, DATE_FORMAT(date_created, '%d %b') AS date FROM news_notice WHERE type='news' ORDER BY id DESC LIMIT 5");
          if ($news->num_rows > 0) {
              while ($row = $news->fetch_assoc()) {
                  echo "<div style='padding: 10px 0; border-bottom: 1px solid #ccc;'>
                          <strong style='color:#002b5c;'>{$row['date']}</strong> - {$row['title']}
                        </div>";
              }
          } else {
              echo "<div>No news available.</div>";
          }
          ?>
        </marquee>
      </div>
    </div>

    <!-- Announcements Section -->
    <div style="flex: 1.2; background: #fff; border: 1px solid #ddd; border-radius: 10px; padding: 20px;">
      <h2 style="font-size: 28px; color: #002b5c; border-bottom: 2px solid #002b5c; margin-bottom: 15px;">Announcements</h2>
      <?php
      $announcements = $conn->query("SELECT title, DATE_FORMAT(date_created, '%d') AS day, DATE_FORMAT(date_created, '%b') AS month FROM news_notice WHERE type='announcement' ORDER BY id DESC LIMIT 5");
      if ($announcements->num_rows > 0) {
          while ($row = $announcements->fetch_assoc()) {
              echo "
              <div style='display: flex; gap: 10px; align-items: center; padding: 10px 0; border-bottom: 1px solid #ccc;'>
                <div style='background: #002b5c; color: #fff; text-align: center; padding: 5px 10px; border-radius: 4px;'>
                  <div style='font-size: 16px;'>{$row['day']}</div>
                  <div style='font-size: 12px;'>{$row['month']}</div>
                </div>
                <div style='flex: 1; font-size: 18px;'>{$row['title']}</div>
                <div style='font-size: 18px;'>➜</div>
              </div>";
          }
      } else {
          echo "<div style='padding: 10px;'>No announcements yet.</div>";
      }
      ?>
    </div>

  </div>
</section>



    <!-- our services -->
     
<a name="services"></a>
    <div class="our-service">
        <div class="service-heading">
            <h2>Our Services</h2>
        </div>

        <div class="main-services">
            <div class="inner-services">
                <div class="service-icon">
                <i class="fas fa-hospital-alt"></i>
                </div>
                <h3>Electronic Health Records (EHR) Management</h3>
                <p>Centralized storage and management of patient records, including medical history, treatments, medications, and test results.</p>
            </div>

            <div class="inner-services">
                <div class="service-icon">
                <i class="fas fa-calendar-check"></i>
                </div>
                <h3>Appointment Scheduling</h3>
                <p>Online booking systems for patients to schedule appointments with healthcare providers, reducing administrative burden and improving patient convenience.</p>
            </div>

            <div class="inner-services">
                <div class="service-icon">
                <i class="fas fa-bed"></i>
                </div>
                <h3>Patient Portal</h3>
                <p>Secure portals where patients can access their health information, communicate with providers, view test results, and manage appointments.</p>
            </div>

            <div class="inner-services">
                <div class="service-icon">
                    <i class="fas fa-notes-medical"></i>
                </div>
                <h3>Workflow Automation</h3>
                <p>Automation of routine administrative tasks, such as appointment reminders, prescription refills, and follow-up notifications, to improve operational efficiency.</p>
            </div>

            <div class="inner-services">
                <div class="service-icon">
                <i class="fas fa-database"></i>
                </div>
                <h3>Data Analytics and Reporting</h3>
                <p>Tools for analyzing healthcare data to identify trends, monitor outcomes, and optimize workflows for better patient care and resource allocation.</p>
            </div>

            <div class="inner-services">
                <div class="service-icon">
                <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Security and Compliance</h3>
                <p>Robust security measures and compliance with healthcare regulations (like HIPAA in the US) to ensure patient data privacy and confidentiality.</p>
            </div>
        </div>
    </div>

    <!-- our services ended -->


    <script src="script.js"></script>

</body>
</html>