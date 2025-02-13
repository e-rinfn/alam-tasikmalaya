<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panorama Editor with Crosshair and Upload</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #panorama-container {
            position: relative;
            width: 100%;
            height: 70vh;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        #panorama {
            width: 100%;
            height: 100%;
        }

        /* Crosshair styles */
        .crosshair {
            position: absolute;
            z-index: 999;
            width: 2px;
            height: 100%;
            background-color: rgba(255, 0, 0, 0.5);
        }

        .crosshair.horizontal {
            width: 100%;
            height: 2px;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-4">
        <h3 class="text-center mb-4">Cari Titik Kordinat</h3>

        <!-- Upload Container -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-6 text-center">
                <label for="upload-input" class="form-label fw-bold">Upload Panorama Image</label>
                <input type="file" id="upload-input" class="form-control" accept="image/*">
            </div>
        </div>

        <!-- Panorama and Controls -->
        <div class="row">
            <!-- Panorama Viewer -->
            <div class="col-lg-8 mb-3">
                <div id="panorama-container" class="position-relative"
                    style="height: 50vh; max-width: 600px; margin: 0 auto;">
                    <div id="panorama"></div>
                    <!-- Crosshair -->
                    <div class="crosshair vertical" style="left: 50%; top: 0;"></div>
                    <div class="crosshair horizontal" style="top: 50%; left: 0;"></div>
                </div>
            </div>


            <!-- Controls -->
            <div class="col-lg-4 text-center">
                <div class="p-3 bg-white border rounded shadow-sm">
                    <button id="setPointer" class="btn btn-primary mb-3 w-100">Set Pointer</button>
                    <div class="text-muted">
                        <p>Pitch: <span id="pitch">0</span></p>
                        <p>Yaw: <span id="yaw">0</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Pannellum JS and CSS -->
    <script src="https://cdn.jsdelivr.net/npm/pannellum/build/pannellum.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum/build/pannellum.css">
    <script>
        let viewer;

        // Initialize viewer with default panorama
        function initializeViewer(panorama) {
            // Destroy the viewer if it already exists
            if (viewer) {
                viewer.destroy();
            }

            // Create new viewer
            viewer = pannellum.viewer('panorama', {
                type: 'equirectangular',
                panorama: panorama,
                autoLoad: true
            });
        }

        // Initialize with default image
        initializeViewer('demo.png');

        let pointerHotspot = null;

        // Handle image upload
        document.getElementById('upload-input').addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const imageUrl = e.target.result;
                    initializeViewer(imageUrl); // Load uploaded image into viewer
                };
                reader.readAsDataURL(file); // Convert image to base64 URL
            }
        });

        // Set pointer functionality
        document.getElementById('setPointer').addEventListener('click', () => {
            const pitch = viewer.getPitch();
            const yaw = viewer.getYaw();

            // Display pitch and yaw
            document.getElementById('pitch').innerText = pitch.toFixed(2);
            document.getElementById('yaw').innerText = yaw.toFixed(2);

            // Remove the old hotspot if it exists
            if (pointerHotspot) {
                viewer.removeHotSpot(pointerHotspot);
            }

            // Add new hotspot (pointer)
            pointerHotspot = 'pointer-' + Date.now();
            viewer.addHotSpot({
                id: pointerHotspot,
                pitch: pitch,
                yaw: yaw,
                type: 'info',
                text: 'Pointer'
            });
        });
    </script>
</body>

</html>
