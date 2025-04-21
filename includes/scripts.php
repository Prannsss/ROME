<!-- Bootstrap core JavaScript-->
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="../js/sb-admin-2.min.js"></script>

<!-- Page level plugins -->
<script src="../vendor/chart.js/Chart.min.js"></script>

<!-- Page level custom scripts -->
<script src="../js/demo/chart-area-demo.js"></script>
<script src="../js/demo/chart-pie-demo.js"></script>

<!-- Custom JavaScript for Room Details Modal -->
<script>
    $(document).ready(function() {
        // Fix for Bootstrap 5 compatibility if needed
        if (typeof bootstrap !== 'undefined') {
            // For Bootstrap 5
            const viewRoomModal = document.getElementById('viewRoomModal');
            if (viewRoomModal) {
                viewRoomModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const roomId = button.getAttribute('data-room-id');
                    const roomName = button.getAttribute('data-room-name');
                    const roomAddress = button.getAttribute('data-room-address');
                    const roomRent = button.getAttribute('data-room-rent');
                    const roomDescription = button.getAttribute('data-room-description');
                    const roomImage = button.getAttribute('data-room-image');
                    
                    const modal = this;
                    modal.querySelector('#roomName').textContent = roomName;
                    modal.querySelector('#roomAddress').textContent = roomAddress;
                    modal.querySelector('#roomRent').textContent = roomRent;
                    modal.querySelector('#roomDescription').textContent = roomDescription;
                    modal.querySelector('#roomImage').src = roomImage;
                    
                    // Set room ID for request button
                    modal.querySelector('#requestRoomBtn').setAttribute('data-room-id', roomId);
                });
                
                // Handle room request
                document.getElementById('requestRoomBtn').addEventListener('click', function() {
                    const roomId = this.getAttribute('data-room-id');
                    // You can implement AJAX request here to submit rental request
                    alert('Your request for this room has been submitted. The property manager will contact you soon.');
                    const modalInstance = bootstrap.Modal.getInstance(viewRoomModal);
                    modalInstance.hide();
                });
            }
        } else {
            // For Bootstrap 4
            $('#viewRoomModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var roomId = button.data('room-id');
                var roomName = button.data('room-name');
                var roomAddress = button.data('room-address');
                var roomRent = button.data('room-rent');
                var roomDescription = button.data('room-description');
                var roomImage = button.data('room-image');
                
                var modal = $(this);
                modal.find('#roomName').text(roomName);
                modal.find('#roomAddress').text(roomAddress);
                modal.find('#roomRent').text(roomRent);
                modal.find('#roomDescription').text(roomDescription);
                modal.find('#roomImage').attr('src', roomImage);
                
                // Set room ID for request button
                modal.find('#requestRoomBtn').data('room-id', roomId);
            });
            
            // Handle room request
            $('#requestRoomBtn').click(function() {
                var roomId = $(this).data('room-id');
                // You can implement AJAX request here to submit rental request
                alert('Your request for this room has been submitted. The property manager will contact you soon.');
                $('#viewRoomModal').modal('hide');
            });
        }
    });
</script>