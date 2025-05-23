document.addEventListener('DOMContentLoaded', function() {
    const vehicles = document.querySelectorAll('.vehicle-anim-bg img');
    const screenWidth = window.innerWidth;
    const screenHeight = window.innerHeight;
    
    // Initialize vehicles with random positions, speeds, and directions
    vehicles.forEach(vehicle => {
        const randomX = Math.random() * screenWidth;
        const randomY = Math.random() * screenHeight;
        const speed = 0.5 + Math.random() * 1.5; // Random speed between 0.5 and 2
        const angle = Math.random() * 2 * Math.PI; // Random direction in radians
        vehicle.style.transform = `translate(${randomX}px, ${randomY}px)`;
        vehicle.dataset.x = randomX;
        vehicle.dataset.y = randomY;
        vehicle.dataset.speed = speed;
        vehicle.dataset.angle = angle;
    });

    let lastTime = performance.now();

    function getBGWidth() {
        return Math.max(
            document.body.scrollWidth,
            document.documentElement.scrollWidth,
            document.body.offsetWidth,
            document.documentElement.offsetWidth,
            document.documentElement.clientWidth
        );
    }
    function getBGHeight() {
        return Math.max(
            document.body.scrollHeight,
            document.documentElement.scrollHeight,
            document.body.offsetHeight,
            document.documentElement.offsetHeight,
            document.documentElement.clientHeight
        );
    }

    function moveVehicles(currentTime) {
        const deltaTime = currentTime - lastTime;
        lastTime = currentTime;
        const width = getBGWidth();
        const height = getBGHeight();

        vehicles.forEach(vehicle => {
            let x = parseFloat(vehicle.dataset.x);
            let y = parseFloat(vehicle.dataset.y);
            let speed = parseFloat(vehicle.dataset.speed);
            let angle = parseFloat(vehicle.dataset.angle);
            const vWidth = vehicle.offsetWidth || 120;
            const vHeight = vehicle.offsetHeight || 60;

            // Move
            x += Math.cos(angle) * speed * deltaTime * 0.1;
            y += Math.sin(angle) * speed * deltaTime * 0.1;

            // Bounce off edges
            let bounced = false;
            if (x < -vWidth) { x = -vWidth; angle = Math.PI - angle; bounced = true; }
            if (x > width) { x = width; angle = Math.PI - angle; bounced = true; }
            if (y < -vHeight) { y = -vHeight; angle = -angle; bounced = true; }
            if (y > height) { y = height; angle = -angle; bounced = true; }
            if (bounced) {
                // Add a little randomness to the angle after bounce
                angle += (Math.random() - 0.5) * 0.5;
            }

            vehicle.dataset.x = x;
            vehicle.dataset.y = y;
            vehicle.dataset.angle = angle;
            vehicle.style.transform = `translate(${x}px, ${y}px)`;
        });

        requestAnimationFrame(moveVehicles);
    }

    requestAnimationFrame(moveVehicles);

    // Handle window resize
    window.addEventListener('resize', function() {
        const width = getBGWidth();
        const height = getBGHeight();
        vehicles.forEach(vehicle => {
            let x = parseFloat(vehicle.dataset.x);
            let y = parseFloat(vehicle.dataset.y);
            const vWidth = vehicle.offsetWidth || 120;
            const vHeight = vehicle.offsetHeight || 60;
            if (x > width) vehicle.dataset.x = width - vWidth;
            if (y > height) vehicle.dataset.y = height - vHeight;
        });
    });
}); 