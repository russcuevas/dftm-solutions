@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: {!! json_encode(session('success')) !!},
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    iconColor: '#059669',
                    customClass: {
                        popup: 'dftm-swal-toast'
                    }
                });
            }
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: {!! json_encode(session('error')) !!},
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    iconColor: '#DC2626',
                    customClass: {
                        popup: 'dftm-swal-toast'
                    }
                });
            }
        });
    </script>
@endif

@if(session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: {!! json_encode(session('info')) !!},
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    iconColor: '#0284C7',
                    customClass: {
                        popup: 'dftm-swal-toast'
                    }
                });
            }
        });
    </script>
@endif

@if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal !== 'undefined') {
                let errorHtml = {!! json_encode(implode('<br>', $errors->all())) !!};
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    html: errorHtml,
                    showConfirmButton: false,
                    timer: 4500,
                    timerProgressBar: true,
                    iconColor: '#DC2626',
                    customClass: {
                        popup: 'dftm-swal-toast'
                    }
                });
            }
        });
    </script>
@endif
