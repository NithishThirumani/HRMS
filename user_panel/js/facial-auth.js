const faceAuth = {
    initCamera: async function() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            const video = document.getElementById('faceAuthVideo');
            video.srcObject = stream;
        } catch (err) {
            console.error('Error accessing camera:', err);
        }
    },
    
    captureFace: function() {
        const video = document.getElementById('faceAuthVideo');
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        return canvas.toDataURL('image/jpeg');
    }
};