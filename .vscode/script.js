const API_BASE = 'http://localhost:5000/api';

// DOM Elements
const startRecordBtn = document.getElementById('startRecord');
const stopRecordBtn = document.getElementById('stopRecord');
const recordingStatus = document.getElementById('recordingStatus');
const uploadForm = document.getElementById('uploadForm');
const resultsSection = document.getElementById('resultsSection');
const loadingElement = document.getElementById('loading');

let mediaRecorder;
let audioChunks = [];

// Event Listeners
startRecordBtn.addEventListener('click', startRecording);
stopRecordBtn.addEventListener('click', stopRecording);
uploadForm.addEventListener('submit', handleFileUpload);

// Recording Functions
async function startRecording() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];

        mediaRecorder.ondataavailable = (event) => {
            audioChunks.push(event.data);
        };

        mediaRecorder.onstop = async () => {
            const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
            await analyzeRecording(audioBlob);
            stream.getTracks().forEach(track => track.stop());
        };

        mediaRecorder.start();
        updateRecordingUI(true);
    } catch (error) {
        showError('Microphone access denied or not available');
    }
}

function stopRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
        updateRecordingUI(false);
    }
}

function updateRecordingUI(isRecording) {
    startRecordBtn.disabled = isRecording;
    stopRecordBtn.disabled = !isRecording;
    
    if (isRecording) {
        recordingStatus.textContent = '● Recording... (60 seconds maximum)';
        recordingStatus.className = 'status recording';
    } else {
        recordingStatus.textContent = 'Processing...';
        recordingStatus.className = 'status';
    }
}

// File Upload Handler
async function handleFileUpload(event) {
    event.preventDefault();
    const fileInput = document.getElementById('audioFile');
    const file = fileInput.files[0];
    
    if (!file) {
        showError('Please select an audio file');
        return;
    }

    await analyzeRecording(file);
}

// Analysis Function
async function analyzeRecording(audioBlob) {
    showLoading(true);
    
    try {
        const formData = new FormData();
        formData.append('audio', audioBlob, 'recording.wav');

        const response = await fetch(`${API_BASE}/analyze-audio`, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`API error: ${response.status}`);
        }

        const data = await response.json();
        displayResults(data);
        
    } catch (error) {
        console.error('Analysis error:', error);
        showError(`Analysis failed: ${error.message}. Make sure Python backend is running on port 5000.`);
    } finally {
        showLoading(false);
    }
}

// Display Results
function displayResults(data) {
    const { transcription, analysis } = data;
    const summary = analysis.summary;

    // Display transcription
    document.getElementById('transcription').textContent = transcription;

    // Display scores
    document.getElementById('overallScore').textContent = formatScore(summary.final_score);
    document.getElementById('clarityScore').textContent = formatScore(summary.speech_clarity);
    document.getElementById('paceScore').textContent = formatScore(summary.pace_score);
    document.getElementById('prosodyScore').textContent = formatScore(summary.prosody_score);

    // Display detailed metrics
    const metricsHtml = `
        <div class="metric-item">
            <span class="metric-label">Words per Minute:</span>
            <span class="metric-value">${summary.wpm ? Math.round(summary.wpm) : 'N/A'}</span>
        </div>
        <div class="metric-item">
            <span class="metric-label">Total Words:</span>
            <span class="metric-value">${summary.total_words}</span>
        </div>
        <div class="metric-item">
            <span class="metric-label">Filler Words:</span>
            <span class="metric-value">${summary.filler_count}</span>
        </div>
        <div class="metric-item">
            <span class="metric-label">Duration:</span>
            <span class="metric-value">${summary.duration_seconds ? Math.round(summary.duration_seconds) + 's' : 'N/A'}</span>
        </div>
        <div class="metric-item">
            <span class="metric-label">Sentiment:</span>
            <span class="metric-value">${formatScore(summary.avg_sentiment_norm)}</span>
        </div>
    `;
    document.getElementById('detailedMetrics').innerHTML = metricsHtml;

    // Display sentiment timeline
    const sentimentHtml = analysis.buckets.map((bucket, index) => `
        <div class="metric-item">
            <span class="metric-label">Segment ${index + 1}:</span>
            <span class="metric-value">${bucket.label} (${formatScore(bucket.score)})</span>
        </div>
    `).join('');
    document.getElementById('sentimentTimeline').innerHTML = sentimentHtml;

    // Show results section
    resultsSection.style.display = 'block';
    resultsSection.scrollIntoView({ behavior: 'smooth' });
}

// Utility Functions
function formatScore(score) {
    return Math.round(score * 100) + '%';
}

function showLoading(show) {
    loadingElement.style.display = show ? 'block' : 'none';
}

function showError(message) {
    recordingStatus.textContent = message;
    recordingStatus.className = 'status error';
    showLoading(false);
}

// Initialize
console.log('🎤 Speech Analysis Frontend Loaded');
console.log('Make sure Python backend is running: python app.py');