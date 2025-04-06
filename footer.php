<?php
// footer.php
?>
    </div> <!-- Close container -->

    <footer style="
        background-color: #1e1e1e;
        color: #f1f1f1;
        text-align: center;
        padding: 1.5rem 1rem;
        margin-top: auto;
        border-top: 1px solid #2c2c2c;
        font-size: 0.95rem;
    ">
        <div style="
            max-width: 800px;
            margin: 0 auto;
        ">
            <p style="margin-bottom: 0.8rem; color: #cccccc;">
                <i class="fas fa-shield-alt" style="margin-right: 6px; color: #4cc9f0;"></i>
                Files auto-delete after 24 hours
            </p>
            <p style="
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
                font-size: 0.85rem;
                color: #aaaaaa;
            ">
                <span>TEMP CLOUD STORAGE &copy; <?php echo date('Y'); ?></span>
            </p>
        </div>
    </footer>

    <script>
        // Show selected file name
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('file-upload');
            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const fileName = e.target.files[0]
                        ? 'Selected: ' + e.target.files[0].name
                        : 'No file selected';
                    const display = document.getElementById('file-name');
                    if (display) display.textContent = fileName;
                });
            }
        });
    </script>
</body>
</html>
