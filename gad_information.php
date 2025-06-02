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
    margin-bottom: 0.6rem;
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
        <li><strong>1. Gender-Based Discrimination:</strong> Unfair treatment based on gender, often resulting in unequal access to opportunities in schools, workplaces, and society.</li>
        <li><strong>2. Lack of Gender Sensitivity:</strong> The absence of awareness or respect for different gender roles and identities, leading to biased decisions or exclusion.</li>
        <li><strong>3. Sexual Harassment and Abuse:</strong> Unwanted sexual behavior or advances that harm an individual's safety, dignity, or mental well-being.</li>
        <li><strong>4. Lack of Safe Spaces:</strong> Environments where individuals feel unsafe, judged, or unable to express themselves freely due to fear or discrimination.</li>
        <li><strong>5. Limited Representation:</strong> Underrepresentation of women and LGBTQ+ individuals in leadership and decision-making roles, limiting diverse perspectives.</li>
        <li><strong>6. Teenage Pregnancy Stigma:</strong> Discrimination or shame directed at young parents that impacts their mental health and academic future.</li>
        <li><strong>7. Others:</strong> This may include gender pay gaps, access to healthcare, or rigid gender stereotypes affecting daily life.</li>
      </ul>
      <p class="note">If you are facing any of these situations, it is considered a <strong>GAD-related concern</strong>.</p>
    </div>

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
        <li><strong>1. Equal Treatment and Opportunities:</strong> Ensuring fairness and equal chances for all genders in education, leadership, and resources.</li>
        <li><strong>2. Gender Sensitivity Education:</strong> Promoting awareness and understanding through educational programs on gender issues and inclusivity.</li>
        <li><strong>3. Safe and Inclusive Spaces:</strong> Creating supportive environments free from judgment, fear, or exclusion.</li>
        <li><strong>4. Access to Gender-Responsive Support Services:</strong> Services tailored to specific gender needs to ensure appropriate and effective support.</li>
        <li><strong>5. Clear Anti-Harassment and Anti-Discrimination Policies:</strong> Strong institutional policies that protect individuals and promote accountability.</li>
        <li><strong>6. Support for Student-Parents:</strong> Academic flexibility, counseling, and childcare support for young parents continuing their education.</li>
        <li><strong>7. Others:</strong> Further efforts may include improved access to healthcare, legal services, and leadership opportunities.</li>
      </ul>
      <p class="note">If you need any of the above, it qualifies as a <strong>GAD-related request</strong>.</p>
    </div>

    <div class="cta-container">
      <span class="cta-text">Would you like to request support or change?</span>
      <a class="cta-button" href="request.php">📝 Submit a GAD Request</a>
    </div>
  </div>
</div>

<?php include_once('temp/footer.php'); ?>
