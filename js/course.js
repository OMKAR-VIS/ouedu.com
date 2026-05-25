const courses = {
  english: [{ t: 'Chapter 1 - First Flight', u: 'https://www.youtube.com/embed/X1wvqloluQ0' }],
  math: [{ t: 'Real Numbers', u: 'https://www.youtube.com/embed/X1wvqloluQ0' }],
  science: [{ t: 'Chemical Reactions', u: 'https://www.youtube.com/embed/X1wvqloluQ0' }],
  social: [{ t: 'Power Sharing', u: 'https://www.youtube.com/embed/X1wvqloluQ0' }],
  hindi: [{ t: 'Hindi Chapter 1', u: 'https://www.youtube.com/embed/X1wvqloluQ0' }],
  computer: [{ t: 'Computer Basics', u: 'https://www.youtube.com/embed/X1wvqloluQ0' }]
};

document.querySelectorAll('.course-card').forEach(card => {
  card.addEventListener('click', (e) => {
    if (e.target.closest('.course-btn')) e.preventDefault();
    loadCourse(card.dataset.course);
  });
});

document.querySelectorAll('.course-btn').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    const card = btn.closest('.course-card');
    if (card) loadCourse(card.dataset.course);
  });
});

function loadCourse(key) {
  const data = courses[key] || [];
  document.getElementById('courseSelection').style.display = 'none';
  document.getElementById('videoPlayerSection').style.display = 'block';
  const list = document.getElementById('videoList');
  list.innerHTML = '';
  data.forEach((video, i) => {
    const el = document.createElement('div');
    el.className = 'video-item' + (i === 0 ? ' active' : '');
    el.textContent = video.t;
    el.addEventListener('click', () => {
      document.querySelectorAll('.video-item').forEach(x => x.classList.remove('active'));
      el.classList.add('active');
      document.getElementById('mainVideo').src = video.u;
    });
    list.appendChild(el);
  });
  if (data[0]) document.getElementById('mainVideo').src = data[0].u;
}

document.getElementById('backBtn')?.addEventListener('click', () => {
  document.getElementById('videoPlayerSection').style.display = 'none';
  document.getElementById('courseSelection').style.display = 'block';
  document.getElementById('mainVideo').src = '';
});
