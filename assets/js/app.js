/*Custom JS*/

// Navbar scroll behavior
$(window).scroll(function() {
  if ($("#mainNav").length) {
    if ($(window).scrollTop() > 50) {
      $("#mainNav").addClass("navbar-shrink");
    } else {
      $("#mainNav").removeClass("navbar-shrink");
    }
  }
});

// Initialize this behavior when the document is ready
$(document).ready(function() {
  // Check initial scroll position
  if ($(window).scrollTop() > 50) {
    $("#mainNav").addClass("navbar-shrink");
  }
});
/*Custom JS*/
// Navbar scroll behavior
$(window).scroll(function() {
  if ($("#mainNav").length) {
    if ($(window).scrollTop() > 50) {
      $("#mainNav").addClass("navbar-shrink");
    } else {
      $("#mainNav").removeClass("navbar-shrink");
    }
  }
});

// Initialize this behavior when the document is ready
$(document).ready(function() {
  // Check initial scroll position
  if ($(window).scrollTop() > 50) {
    $("#mainNav").addClass("navbar-shrink");
  }
  
  // Initialize dashboard functionality if on dashboard page
  if ($("#dashboardTabContent").length) {
    initDashboard();
  }
});

// Dashboard initialization function
function initDashboard() {
  // Toggle sidebar on mobile
  const sidebarToggle = document.getElementById('sidebarToggle');
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
      document.body.classList.toggle('sb-sidenav-toggled');
    });
  }
  
  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
  
  // Initialize dashboard charts
  initCharts();
  
  // Handle profile editing
  initProfileEditing();
  
  // Handle profile picture change
  initProfilePictureUpload();
  
  // Handle payment method selection
  initPaymentMethodSelection();
  
  // Handle bill payment
  initBillPayment();
  
  // Handle payment processing
  initPaymentProcessing();
  
  // Handle view bill details
  initBillDetailsView();
  
  // Handle view receipt
  initReceiptView();
  
  // Handle maintenance request submission
  initMaintenanceRequestSubmission();
  
  // Handle visitor registration
  initVisitorRegistration();
  
  // Handle cancel visitor
  initCancelVisitor();
  
  // Handle room reservation
  initRoomReservation();
}

// Initialize profile editing functionality
function initProfileEditing() {
  const editProfileBtn = document.getElementById('editProfileBtn');
  const cancelEditBtn = document.getElementById('cancelEditBtn');
  const profileFormButtons = document.getElementById('profileFormButtons');
  const profileForm = document.getElementById('profileForm');
  
  if (editProfileBtn) {
    editProfileBtn.addEventListener('click', function() {
      const formInputs = profileForm.querySelectorAll('input, select');
      formInputs.forEach(input => {
        input.disabled = false;
      });
      profileFormButtons.style.display = 'block';
      editProfileBtn.style.display = 'none';
    });
  }
  
  if (cancelEditBtn) {
    cancelEditBtn.addEventListener('click', function() {
      const formInputs = profileForm.querySelectorAll('input, select');
      formInputs.forEach(input => {
        input.disabled = true;
      });
      profileFormButtons.style.display = 'none';
      editProfileBtn.style.display = 'inline-block';
    });
  }
  
  if (profileForm) {
    profileForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(profileForm);
      
      fetch('update_profile.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Profile updated successfully!');
          location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating your profile.');
      });
    });
  }
}

// Initialize profile picture upload functionality
function initProfilePictureUpload() {
  // Look for both possible elements - the one in the HTML and the one your JS is looking for
  const profileImageUpload = document.getElementById('profileImageUpload');
  
  if (profileImageUpload) {
    // Add a direct event listener to the file input
    profileImageUpload.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const formData = new FormData();
        formData.append('profile_picture', this.files[0]);
        
        // Add a loading indicator or message
        const profilePicDisplay = document.getElementById('profilePictureDisplay');
        if (profilePicDisplay) {
          profilePicDisplay.style.opacity = '0.5'; // Dim the image during upload
        }
        
        // Log to console for debugging
        console.log('Uploading profile picture...');
        
        fetch('/ROME/api/upload_profile_picture.php', {
          method: 'POST',
          body: formData
        })
        .then(response => {
          console.log('Response status:', response.status);
          return response.json();
        })
        .then(data => {
          console.log('Upload response:', data);
          if (data.success) {
            alert('Profile picture updated successfully!');
            location.reload();
          } else {
            alert('Error: ' + data.message);
            // Reset opacity if upload fails
            if (profilePicDisplay) {
              profilePicDisplay.style.opacity = '1';
            }
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while updating your profile picture.');
          // Reset opacity if upload fails
          if (profilePicDisplay) {
            profilePicDisplay.style.opacity = '1';
          }
        });
      }
    });
  } else {
    console.error('Profile image upload element not found in the DOM');
  }
}

// Initialize payment method selection functionality
function initPaymentMethodSelection() {
  const paymentMethod = document.getElementById('payment_method');
  const paymentSections = document.querySelectorAll('.payment-section');
  
  if (paymentMethod) {
    paymentMethod.addEventListener('change', function() {
      paymentSections.forEach(section => {
        section.style.display = 'none';
      });
      
      const selectedMethod = this.value;
      if (selectedMethod) {
        document.getElementById(selectedMethod + '_section').style.display = 'block';
      }
    });
  }
}

// Initialize bill payment functionality
function initBillPayment() {
  const payBillButtons = document.querySelectorAll('.pay-bill');
  const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
  
  payBillButtons.forEach(button => {
    button.addEventListener('click', function() {
      const billId = this.getAttribute('data-bill-id');
      document.getElementById('payment_bill_id').value = billId;
      
      // Fetch bill details
      fetch('get_bill_details.php?bill_id=' + billId)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            document.getElementById('payment_description').textContent = data.bill.description;
            document.getElementById('payment_amount').textContent = parseFloat(data.bill.amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            paymentModal.show();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while fetching bill details.');
        });
    });
  });
}

// Initialize payment processing functionality
function initPaymentProcessing() {
  const processPaymentBtn = document.getElementById('processPayment');
  
  if (processPaymentBtn) {
    processPaymentBtn.addEventListener('click', function() {
      const paymentForm = document.getElementById('paymentForm');
      const formData = new FormData(paymentForm);
      
      fetch('process_payment.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Payment processed successfully!');
          location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your payment.');
      });
    });
  }
}

// Initialize bill details view functionality
function initBillDetailsView() {
  const viewBillDetailsButtons = document.querySelectorAll('.view-bill-details');
  const billDetailsModal = new bootstrap.Modal(document.getElementById('billDetailsModal'));
  
  viewBillDetailsButtons.forEach(button => {
    button.addEventListener('click', function() {
      const billId = this.getAttribute('data-bill-id');
      
      fetch('get_bill_details.php?bill_id=' + billId)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            document.getElementById('bill_id').textContent = data.bill.id;
            document.getElementById('bill_type').textContent = data.bill.type;
            document.getElementById('bill_description').textContent = data.bill.description;
            document.getElementById('bill_amount').textContent = parseFloat(data.bill.amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('bill_due_date').textContent = data.bill.due_date;
            document.getElementById('bill_status').textContent = data.bill.status;
            
            // Handle breakdown if available
            const breakdownSection = document.getElementById('bill_breakdown_section');
            const breakdownBody = document.getElementById('bill_breakdown');
            
            if (data.bill.breakdown && data.bill.breakdown.length > 0) {
              breakdownBody.innerHTML = '';
              data.bill.breakdown.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                  <td>${item.description}</td>
                  <td>₱${parseFloat(item.amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                `;
                breakdownBody.appendChild(row);
              });
              breakdownSection.style.display = 'block';
            } else {
              breakdownSection.style.display = 'none';
            }
            
            // Handle late fee if applicable
            const lateFeeSection = document.getElementById('bill_late_fee_section');
            
            if (data.bill.late_fee && parseFloat(data.bill.late_fee) > 0) {
              document.getElementById('bill_late_fee').textContent = parseFloat(data.bill.late_fee).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
              lateFeeSection.style.display = 'block';
            } else {
              lateFeeSection.style.display = 'none';
            }
            
            // Set up pay button
            const payBillFromDetailsBtn = document.getElementById('payBillFromDetails');
            payBillFromDetailsBtn.setAttribute('data-bill-id', data.bill.id);
            
            billDetailsModal.show();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while fetching bill details.');
        });
    });
  });
  
  // Handle pay button in bill details modal
  const payBillFromDetailsBtn = document.getElementById('payBillFromDetails');
  if (payBillFromDetailsBtn) {
    payBillFromDetailsBtn.addEventListener('click', function() {
      const billId = this.getAttribute('data-bill-id');
      const payBillBtn = document.querySelector(`.pay-bill[data-bill-id="${billId}"]`);
      
      if (payBillBtn) {
        billDetailsModal.hide();
        setTimeout(() => {
          payBillBtn.click();
        }, 500);
      }
    });
  }
}

// Initialize receipt view functionality
function initReceiptView() {
  const viewReceiptButtons = document.querySelectorAll('.view-receipt');
  const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
  
  viewReceiptButtons.forEach(button => {
    button.addEventListener('click', function() {
      const paymentId = this.getAttribute('data-payment-id');
      
      fetch('get_receipt.php?payment_id=' + paymentId)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            document.getElementById('receipt_id').textContent = data.receipt.id;
            document.getElementById('receipt_date').textContent = data.receipt.date;
            document.getElementById('receipt_tenant').textContent = data.receipt.tenant_name;
            document.getElementById('receipt_description').textContent = data.receipt.description;
            document.getElementById('receipt_amount').textContent = parseFloat(data.receipt.amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('receipt_method').textContent = data.receipt.payment_method;
            document.getElementById('receipt_transaction_id').textContent = data.receipt.transaction_id;
            
            receiptModal.show();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while fetching receipt details.');
        });
    });
  });
  
  // Handle print receipt
  const printReceiptBtn = document.getElementById('printReceipt');
  if (printReceiptBtn) {
    printReceiptBtn.addEventListener('click', function() {
      const receiptContent = document.getElementById('receiptContent').innerHTML;
      const printWindow = window.open('', '_blank');
      
      printWindow.document.write(`
        <html>
          <head>
            <title>Payment Receipt</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
            <style>
              body { padding: 20px; }
              @media print {
                body { padding: 0; }
                .no-print { display: none; }
              }
            </style>
          </head>
          <body>
            <div class="container">
              ${receiptContent}
              <div class="row mt-4 no-print">
                <div class="col-12 text-center">
                  <button class="btn btn-primary" onclick="window.print()">Print</button>
                  <button class="btn btn-secondary ms-2" onclick="window.close()">Close</button>
                </div>
              </div>
            </div>
          </body>
        </html>
      `);
      
      printWindow.document.close();
    });
  }
  
  // Handle download receipt
  const downloadReceiptBtn = document.getElementById('downloadReceipt');
  if (downloadReceiptBtn) {
    downloadReceiptBtn.addEventListener('click', function() {
      const receiptId = document.getElementById('receipt_id').textContent;
      
      fetch('download_receipt.php?receipt_id=' + receiptId)
        .then(response => response.blob())
        .then(blob => {
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.style.display = 'none';
          a.href = url;
          a.download = 'Receipt_' + receiptId + '.pdf';
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while downloading the receipt.');
        });
    });
  }
}

// Initialize maintenance request submission functionality
function initMaintenanceRequestSubmission() {
  const submitMaintenanceRequestBtn = document.getElementById('submitMaintenanceRequest');
  const maintenanceRequestModal = new bootstrap.Modal(document.getElementById('maintenanceRequestModal'));
  
  if (submitMaintenanceRequestBtn) {
    submitMaintenanceRequestBtn.addEventListener('click', function() {
      const maintenanceRequestForm = document.getElementById('maintenanceRequestForm');
      const formData = new FormData(maintenanceRequestForm);
      
      fetch('add_maintenance_request.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Maintenance request submitted successfully!');
          maintenanceRequestModal.hide();
          location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the maintenance request.');
      });
    });
  }
}

// Initialize visitor registration functionality
function initVisitorRegistration() {
  const submitVisitorLogBtn = document.getElementById('submitVisitorLog');
  const visitorLogModal = new bootstrap.Modal(document.getElementById('visitorLogModal'));
  
  if (submitVisitorLogBtn) {
    submitVisitorLogBtn.addEventListener('click', function() {
      const visitorLogForm = document.getElementById('visitorLogForm');
      const formData = new FormData(visitorLogForm);
      
      fetch('add_visitor.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Visitor registered successfully!');
          visitorLogModal.hide();
          location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while registering the visitor.');
      });
    });
  }
}

// Initialize cancel visitor functionality
function initCancelVisitor() {
  const cancelVisitorButtons = document.querySelectorAll('.cancel-visitor');
  
  cancelVisitorButtons.forEach(button => {
    button.addEventListener('click', function() {
      if (confirm('Are you sure you want to cancel this visitor registration?')) {
        const visitorId = this.getAttribute('data-visitor-id');
        
        fetch('cancel_visitor.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'visitor_id=' + visitorId
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Visitor registration cancelled successfully!');
            location.reload();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while cancelling the visitor registration.');
        });
      }
    });
  });
}

// Initialize room reservation functionality
function initRoomReservation() {
  const submitReservationBtn = document.getElementById('submitReservation');
  const reservationModal = new bootstrap.Modal(document.getElementById('reservationModal'));
  
  if (submitReservationBtn) {
    submitReservationBtn.addEventListener('click', function() {
      const reservationForm = document.getElementById('reservationForm');
      const formData = new FormData(reservationForm);
      
      fetch('add_reservation.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Reservation submitted successfully!');
          reservationModal.hide();
          location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the reservation.');
      });
    });
  }
}

// Initialize dashboard charts
function initCharts() {
  // Rent payment history chart
  const rentHistoryCtx = document.getElementById('rentHistoryChart');
  if (rentHistoryCtx) {
    const rentHistoryChart = new Chart(rentHistoryCtx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Rent Payments',
          data: [5000, 5000, 5000, 5000, 5000, 5000],
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  }
  
  // Utility usage chart
  const utilityUsageCtx = document.getElementById('utilityUsageChart');
  if (utilityUsageCtx) {
    const utilityUsageChart = new Chart(utilityUsageCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [
          {
            label: 'Electricity (kWh)',
            data: [120, 130, 125, 135, 140, 130],
            borderColor: 'rgba(255, 99, 132, 1)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
          },
          {
            label: 'Water (cu.m)',
            data: [8, 9, 8.5, 9.5, 10, 9],
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.1
          }
        ]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  }
}