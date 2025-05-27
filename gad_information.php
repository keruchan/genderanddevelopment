<?php require 'connecting/connect.php'; ?>
<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigation.php'); ?>

<style>
  body {
    background-color: #f0f4f8;
    font-family: 'Segoe UI', sans-serif;
    color: #333;
    font-size: 1.2rem;
  }

  .info-container {
    max-width: 960px;
    margin: 1.5rem auto;
    padding: 2rem;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
    line-height: 1.6;
  }

  .info-container h1 {
    text-align: center;
    color: #2f855a;
    font-size: 2.4rem;
    margin-bottom: 1.5rem;
  }

  .section {
    margin-bottom: 2rem;
  }

  .section-title {
    font-size: 1.5rem;
    color: #2d3748;
    border-left: 5px solid #38a169;
    padding-left: 12px;
    margin-bottom: 0.6rem;
  }

  .highlight-box {
    background-color: #f0fff4;
    border-left: 5px solid #38a169;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-size: 1.1rem;
  }

  .info-list {
    margin: 0;
    padding-left: 1.3rem;
    font-size: 1.15rem;
  }

  .info-list li {
    margin-bottom: 0.4rem;
    color: #444;
  }

  .note {
    font-style: italic;
    font-size: 1.05rem;
    color: #555;
    margin-top: 0.5rem;
  }

  .cta-container {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 0.8rem;
  }

  .cta-text {
    font-size: 1.1rem;
    color: #444;
  }

  .cta-button {
    background-color: #38a169;
    color: white;
    padding: 0.7rem 1.6rem;
    font-size: 1.15rem;
    text-decoration: none;
    border-radius: 8px;
    transition: background 0.3s ease;
  }

  .cta-button:hover {
    background-color: #2f855a;
  }

  @media (max-width: 768px) {
    .info-container {
      padding: 1.5rem;
    }

    .info-container h1 {
      font-size: 2rem;
    }

    .section-title {
      font-size: 1.3rem;
    }

    .cta-container {
      flex-direction: column;
      align-items: flex-start;
    }

    .cta-button {
      width: 100%;
      text-align: center;
    }
  }
</style>

<div class="info-container">
  <h1>Gender and Development (GAD) Services</h1>

  <!-- Section: GAD Concerns -->
  <div class="section">
    <h2 class="section-title">💬 GAD-Related Concerns</h2>
    <div class="highlight-box">
      <p>These are challenges or issues you might encounter related to gender and development:</p>
      <ul class="info-list">
        <li>Unfair or unequal treatment based on gender, sexual orientation, or family status</li>
        <li>Bullying, harassment, or discrimination based on identity</li>
        <li>Feeling unsafe or excluded due to pregnancy, disability, or gender expression</li>
        <li>Stigma or judgment linked to gender roles or identity</li>
      </ul>
      <p class="note">If you are facing any of these situations, it is considered a <strong>GAD-related concern</strong>.</p>
    </div>

    <h3 class="section-title" style="font-size: 1.2rem;">Common Concerns</h3>
    <ul class="info-list">
      <li>1. Gender-based discrimination</li>
      <li>2. Lack of gender sensitivity</li>
      <li>3. Sexual harassment and abuse</li>
      <li>4. Lack of safe spaces</li>
      <li>5. Limited representation</li>
      <li>6. Teenage pregnancy stigma</li>
      <li>7. Others</li>
    </ul>

    <div class="cta-container">
      <span class="cta-text">Have you experienced something similar?</span>
      <a class="cta-button" href="concern.php">📄 Submit a GAD Concern</a>
    </div>
  </div>

  <!-- Section: GAD Requests -->
  <div class="section">
    <h2 class="section-title">📢 GAD-Related Requests</h2>
    <div class="highlight-box">
      <p>These are requests or support services that can help improve your experience as a student:</p>
      <ul class="info-list">
        <li>Fair treatment and equal opportunities for all identities</li>
        <li>Safe and inclusive environments for LGBTQ+ students, student-parents, and others</li>
        <li>Awareness programs and education on gender sensitivity and inclusion</li>
        <li>Flexible and responsive academic policies that accommodate diverse needs</li>
      </ul>
      <p class="note">If you need any of the above, it qualifies as a <strong>GAD-related request</strong>.</p>
    </div>

    <h3 class="section-title" style="font-size: 1.2rem;">Common Requests</h3>
    <ul class="info-list">
      <li>1. Equal treatment and opportunities</li>
      <li>2. Gender sensitivity education</li>
      <li>3. Safe and inclusive spaces</li>
      <li>4. Access to gender-responsive support services</li>
      <li>5. Clear anti-harassment and anti-discrimination policies</li>
      <li>6. Support for student-parents</li>
      <li>7. Others</li>
    </ul>

    <div class="cta-container">
      <span class="cta-text">Would you like to request support or change?</span>
      <a class="cta-button" href="request.php">📝 Submit a GAD Request</a>
    </div>
  </div>
</div>

<?php include_once('temp/footer.php'); ?>
