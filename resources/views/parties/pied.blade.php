</div><!-- /.app -->
<!-- BEGIN BASE JS -->
<script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/vendor/popper.js/umd/popper.min.js') }}"></script>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.min.js') }}"></script> <!-- END BASE JS -->
<!-- BEGIN PLUGINS JS -->
<script src="{{ asset('assets/vendor/pace-progress/pace.min.js') }}"></script>
<script src="{{ asset('assets/vendor/stacked-menu/js/stacked-menu.min.js') }}"></script>
<script src="{{ asset('assets/vendor/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/vendor/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('assets/vendor/easy-pie-chart/jquery.easypiechart.min.js') }}"></script>
<script src="{{ asset('assets/vendor/chart.js/Chart.min.js') }}"></script> <!-- END PLUGINS JS -->
<!-- BEGIN THEME JS -->
<script src="{{ asset('assets/javascript/theme.min.js') }}"></script> <!-- END THEME JS -->
<script  src="{{ asset('assets/custom/sweetalert2/dist/sweetalert2.all.min.js') }}"></script>
<!-- BEGIN PAGE LEVEL JS -->
@yield("script")
<script>
    var skin = localStorage.getItem('skin') || 'default';
    var isCompact = JSON.parse(localStorage.getItem('hasCompactMenu'));
    var disabledSkinStylesheet = document.querySelector('link[data-skin]:not([data-skin="' + skin + '"])');
    // Disable unused skin immediately
    disabledSkinStylesheet.setAttribute('rel', '');
    disabledSkinStylesheet.setAttribute('disabled', true);
    // add flag class to html immediately
    if (isCompact == true) document.querySelector('html').classList.add('preparing-compact-menu');
</script>

</body>
</html>
