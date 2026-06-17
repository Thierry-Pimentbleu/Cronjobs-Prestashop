(function () {
    'use strict';

    var modal, progressBar, progressNumber, stepLabel, btnStart, btnClose, resultDiv;
    var currentProgress = 0;
    var animFrameId;

    function init() {
        modal          = document.getElementById('pbCronjobsUpdateModal');
        progressBar    = document.getElementById('pbCronjobsProgressBar');
        progressNumber = document.getElementById('pbCronjobsProgressNumber');
        stepLabel      = document.getElementById('pbCronjobsStepLabel');
        btnStart       = document.getElementById('pbCronjobsBtnUpdate');
        btnClose       = document.getElementById('pbCronjobsBtnClose');
        resultDiv      = document.getElementById('pbCronjobsResult');

        if (!btnStart) return;

        btnStart.addEventListener('click', function () {
            openModal();
            runUpdate();
        });
        if (btnClose) {
            btnClose.addEventListener('click', closeModal);
        }
    }

    function openModal() {
        currentProgress = 0;
        progressBar.value = 0;
        progressNumber.textContent = '0%';
        stepLabel.textContent = '';
        resultDiv.innerHTML = '';
        btnStart.disabled = true;
        btnClose.disabled = true;
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
        btnStart.disabled = false;
    }

    function setProgress(target) {
        if (animFrameId) cancelAnimationFrame(animFrameId);
        var start = currentProgress;
        var startTime = performance.now();
        var duration = 400;
        function animate(now) {
            var elapsed = now - startTime;
            var fraction = Math.min(elapsed / duration, 1);
            var value = start + (target - start) * fraction;
            currentProgress = value;
            progressBar.value = value;
            progressNumber.textContent = Math.round(value) + '%';
            if (fraction < 1) animFrameId = requestAnimationFrame(animate);
        }
        animFrameId = requestAnimationFrame(animate);
    }

    function callStep(endpoint) {
        return new Promise(function (resolve, reject) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', pbCronjobsUpdatePath + endpoint, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.timeout = 60000;
            xhr.onload = function () {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        resolve();
                    } else {
                        reject(data.error || 'unknown error');
                    }
                } catch (e) {
                    reject('Invalid response');
                }
            };
            xhr.onerror = function () { reject('Network error'); };
            xhr.ontimeout = function () { reject('Timeout'); };
            xhr.send('nonce=' + encodeURIComponent(pbCronjobsUpdateNonce));
        });
    }

    function runUpdate() {
        var steps = [
            { text: pbCronjobsI18n.downloading,   endpoint: 'download_files.php',  target: 40  },
            { text: pbCronjobsI18n.updatingFiles,  endpoint: 'update_files.php',    target: 75  },
            { text: pbCronjobsI18n.updatingDb,     endpoint: 'update_database.php', target: 100 },
        ];

        var i = 0;
        function next() {
            if (i >= steps.length) {
                setProgress(100);
                stepLabel.textContent = pbCronjobsI18n.done;
                resultDiv.innerHTML = '<div class="alert alert-success" style="margin-top:10px;">' + pbCronjobsI18n.success + '</div>';
                btnClose.textContent = pbCronjobsI18n.reload;
                btnClose.disabled = false;
                btnClose.addEventListener('click', function () { window.location.reload(); }, { once: true });
                return;
            }
            var step = steps[i++];
            stepLabel.textContent = step.text;
            callStep(step.endpoint)
                .then(function () {
                    setProgress(step.target);
                    next();
                })
                .catch(function (err) {
                    resultDiv.innerHTML = '<div class="alert alert-danger" style="margin-top:10px;">' + pbCronjobsI18n.updateError + ' ' + err + '</div>';
                    btnClose.disabled = false;
                });
        }
        next();
    }

    document.addEventListener('DOMContentLoaded', init);
})();
