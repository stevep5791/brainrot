<?php
require_once __DIR__ . '/../classes/Auth.php';
$auth = Auth::getInstance();
$isAdmin = $auth->isAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Brain Rot ML Training Data Collection</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
    <?php if ($isAdmin): ?>
    <style>
        .admin-bar {
            background: var(--navy-dark, #0d2137);
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 0.9rem;
            margin: -30px -20px 20px -20px;
            gap: 15px;
        }
        .admin-bar a {
            color: #a0c4ff;
            text-decoration: none;
        }
        .admin-bar a:hover {
            text-decoration: underline;
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <?php if ($isAdmin): ?>
        <!-- Admin Bar (only visible to logged-in admins) -->
        <div class="admin-bar">
            <a href="/admin/">Admin Panel</a>
            <a href="/auth/logout.php">Logout</a>
        </div>
        <?php endif; ?>

        <!-- Logo Section - Presidential AI Challenge Branding -->
        <div class="logo-section">
            <img src="images/logos/presidential-ai-challenge.png" alt="Presidential AI Challenge">
            <img src="images/logos/ai-gov-logo.png" alt="AI.GOV">
        </div>

        <header>
            <h1>Brain Rot ML Training Data Collection</h1>
            <div class="privacy-notice">
                <strong>Privacy Notice:</strong> This website does not track or store any personal data. 
                All submissions are anonymous and used solely for educational purposes.
            </div>
        </header>

        <main>
            <form id="brainrot-form" enctype="multipart/form-data">
                <div class="submission-area">
                    <h2>Submit Brain Rot Content</h2>
                    
                    <div class="input-tabs">
                        <button type="button" class="tab-btn active" data-tab="text">Text Content</button>
                        <button type="button" class="tab-btn" data-tab="image">Image Content</button>
                    </div>

                    <div id="text-tab" class="tab-content active">
                        <div class="text-input-area">
                            <textarea id="text-content" name="text_content" 
                                placeholder="Paste brain rot content from Facebook, Instagram, TikTok, etc...

Mobile: Long-press in this area and select 'Paste'
Desktop: Ctrl+V (Cmd+V on Mac)

Include captions, comments, hashtags, or any toxic text content..." 
                                rows="8"></textarea>
                            <div class="paste-hint">
                                <i>Can also paste images directly into this text area on some devices</i>
                            </div>
                        </div>
                    </div>

                    <div id="image-tab" class="tab-content">
                        <div class="mobile-hints">
                            <div class="social-media-tips">
                                <h4>Mobile Tips for Social Media Content:</h4>
                                <ul>
                                    <li><strong>Images:</strong> Long-press → Copy Image → Paste in text area above</li>
                                    <li><strong>Screenshots:</strong> Take screenshot → Use "Select File" below</li>
                                    <li><strong>Text Posts:</strong> Copy text → Paste in text area above</li>
                                </ul>
                            </div>
                        </div>

                        <div class="file-drop-area" id="file-drop-area">
                            <div class="drop-zone">
                                <div class="drop-icon">📁</div>
                                <p>Select an image from your device</p>
                                <p>or drag and drop here</p>
                                <button type="button" class="select-file-btn">Select Image File</button>
                                <input type="file" id="image-input" name="image_content"
                                    accept="image/*" style="display: none;">
                            </div>
                            <div class="file-preview" id="file-preview" style="display: none;">
                                <img id="preview-image" alt="Preview" style="display: none;">
                                <div class="file-info" id="file-info"></div>
                                <button type="button" class="remove-file-btn">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="categories-section">
                    <h3>Select Brain Rot Categories</h3>
                    <p class="category-instruction">Check all categories that apply to your submission:</p>
                    
                    <div class="categories-grid">
                        <label class="category-item">
                            <input type="checkbox" name="categories[]" value="misinformation">
                            <div class="category-content">
                                <strong>1. Misinformation & Conspiracy</strong>
                                <span>False information, conspiracy theories, and misleading claims that spread rapidly</span>
                            </div>
                        </label>

                        <label class="category-item">
                            <input type="checkbox" name="categories[]" value="toxic">
                            <div class="category-content">
                                <strong>2. Toxic Negativity & Trolling</strong>
                                <span>Hateful comments, cyberbullying, and deliberate inflammatory content</span>
                            </div>
                        </label>

                        <label class="category-item">
                            <input type="checkbox" name="categories[]" value="clickbait">
                            <div class="category-content">
                                <strong>3. Clickbait & Outrage Bait</strong>
                                <span>Sensationalized headlines and content designed to provoke strong emotional reactions</span>
                            </div>
                        </label>

                        <label class="category-item">
                            <input type="checkbox" name="categories[]" value="superficial">
                            <div class="category-content">
                                <strong>4. Superficial Trends & Vanity Loops</strong>
                                <span>Appearance-focused content and shallow trend-chasing that promotes superficial values</span>
                            </div>
                        </label>

                        <label class="category-item">
                            <input type="checkbox" name="categories[]" value="meme_overload">
                            <div class="category-content">
                                <strong>5. Meme Overload & Context Loss</strong>
                                <span>Overwhelming volume of memes that lose original meaning and context</span>
                            </div>
                        </label>

                        <label class="category-item">
                            <input type="checkbox" name="categories[]" value="doomscrolling">
                            <div class="category-content">
                                <strong>6. Doomscrolling & Anxiety Loops</strong>
                                <span>Content that promotes endless scrolling and increases anxiety or negative emotions</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" id="submit-btn">Submit for ML Training</button>
                    <button type="button" id="clear-btn">Clear Form</button>
                </div>

                <div id="form-message" class="form-message"></div>
            </form>
        </main>

        <footer>
            <div class="privacy-details">
                <h4>Privacy & Data Protection</h4>
                <ul>
                    <li>No personal identifiers are collected</li>
                    <li>No IP addresses are logged</li>
                    <li>No cookies or tracking scripts are used</li>
                    <li>All submissions are anonymous</li>
                    <li>Data is used exclusively for educational ML training</li>
                </ul>
            </div>
        </footer>
    </div>

    <script src="js/app.js"></script>
</body>
</html>
