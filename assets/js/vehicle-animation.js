document.addEventListener('DOMContentLoaded', function() {
    const vehicles = document.querySelectorAll('.vehicle-anim-bg img');
    const screenWidth = window.innerWidth;
    
    // Initialize vehicles with random positions and speeds
    vehicles.forEach(vehicle => {
        const randomX = Math.random() * screenWidth;
        const speed = 0.5 + Math.random() * 1.5; // Random speed between 0.5 and 2
        vehicle.style.transform = `translateX(${randomX}px)`;
        vehicle.dataset.speed = speed;
    });

    let lastTime = performance.now();

    // Function to move vehicles continuously
    function moveVehicles(currentTime) {
        const deltaTime = currentTime - lastTime;
        lastTime = currentTime;

        vehicles.forEach(vehicle => {
            // Get current position
            const currentTransform = vehicle.style.transform;
            const currentX = parseFloat(currentTransform.match(/translateX\(([-\d.]+)px/)?.[1] || 0);
            const speed = parseFloat(vehicle.dataset.speed);
            
            // Calculate new position with varying speeds
            let newX = currentX + (speed * deltaTime * 0.1); // Scale speed with time
            
            // Reset position if vehicle goes off screen
            if (newX > screenWidth + 200) {
                newX = -200;
                // Randomize speed when vehicle resets
                vehicle.dataset.speed = 0.5 + Math.random() * 1.5;
            }
            
            // Apply new position
            vehicle.style.transform = `translateX(${newX}px)`;
        });
        
        // Continue animation
        requestAnimationFrame(moveVehicles);
    }

    // Start the animation
    requestAnimationFrame(moveVehicles);

    // Handle window resize
    window.addEventListener('resize', function() {
        const newScreenWidth = window.innerWidth;
        
        vehicles.forEach(vehicle => {
            const currentTransform = vehicle.style.transform;
            const currentX = parseFloat(currentTransform.match(/translateX\(([-\d.]+)px/)?.[1] || 0);
            
            // Adjust position if vehicle is off screen after resize
            if (currentX > newScreenWidth + 200) {
                vehicle.style.transform = `translateX(${newScreenWidth + 200}px)`;
            }
        });
    });
}); 