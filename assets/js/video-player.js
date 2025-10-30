// Video Player JavaScript
function trackVideoProgress(videoElement, lessonId) {
    videoElement.addEventListener('ended', function() {
        console.log('Video completed for lesson:', lessonId);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const videos = document.querySelectorAll('video[data-lesson-id]');
    videos.forEach(function(video) {
        const lessonId = video.dataset.lessonId;
        trackVideoProgress(video, lessonId);
        video.controls = true;
    });
});

console.log('Video Player JavaScript Loaded');
