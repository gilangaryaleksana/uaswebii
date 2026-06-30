import "./bootstrap";

const element = document.querySelector(".element");

if (element) {
    let angle = 220;
    let direction = 1;
    const minAngle = 220;
    const maxAngle = 390;
    const speed = 0.5;

    function animate() {
        angle += direction * speed;
        element.style.setProperty("--angle", `${angle}deg`);

        if (angle >= maxAngle) direction = -1;
        if (angle <= minAngle) direction = 1;

        requestAnimationFrame(animate);
    }

    animate();
}
