// Chemical Inventory System JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Auto-format CAS numbers
    const casInputs = document.querySelectorAll('input[name="cas_number"]');
    casInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/-/g, '');
            if (value.length > 2) {
                value = value.substring(0, value.length - 3) + '-' + 
                        value.substring(value.length - 3, value.length - 1) + '-' + 
                        value.substring(value.length - 1);
            }
            e.target.value = value;
        });
    });
    
    // Auto-calculate molecular weight from formula (basic)
    const formulaInputs = document.querySelectorAll('input[name="formula"]');
    const weightInputs = document.querySelectorAll('input[name="molecular_weight"]');
    
    if (formulaInputs.length && weightInputs.length) {
        formulaInputs[0].addEventListener('blur', function() {
            if (!weightInputs[0].value) {
                const formula = this.value;
                const weight = estimateMolecularWeight(formula);
                if (weight) {
                    weightInputs[0].value = weight.toFixed(2);
                }
            }
        });
    }
    
    // Search functionality
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                this.closest('form').submit();
            }
        });
    });
    
    // Auto-refresh dashboard every 5 minutes
    if (window.location.pathname.includes('dashboard.php')) {
        setInterval(() => {
            // Only refresh if page is visible
            if (!document.hidden) {
                fetch(window.location.href)
                    .then(response => response.text())
                    .then(html => {
                        // Could implement partial refresh here
                        console.log('Dashboard auto-refresh check');
                    });
            }
        }, 300000); // 5 minutes
    }
    
    // Print functionality
    const printButtons = document.querySelectorAll('.print-btn');
    printButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            window.print();
        });
    });
    
    // Tooltip initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Popover initialization
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Quantity validation for transactions
    const quantityInputs = document.querySelectorAll('input[name="quantity_change"]');
    quantityInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const type = document.querySelector('select[name="type"]');
            const currentQuantity = parseFloat(document.querySelector('#current-quantity')?.textContent) || 0;
            
            if (type && type.value === 'checkout' && parseFloat(this.value) > currentQuantity) {
                this.setCustomValidity(`Insufficient stock. Available: ${currentQuantity}`);
            } else {
                this.setCustomValidity('');
            }
        });
    });
});

function estimateMolecularWeight(formula) {
    // Very basic molecular weight estimation
    // This is a simplified version - for production, use a proper chemical formula parser
    const atomicWeights = {
        'H': 1.008, 'He': 4.0026, 'Li': 6.94, 'Be': 9.0122, 'B': 10.81,
        'C': 12.011, 'N': 14.007, 'O': 15.999, 'F': 18.998, 'Ne': 20.180,
        'Na': 22.990, 'Mg': 24.305, 'Al': 26.982, 'Si': 28.085, 'P': 30.974,
        'S': 32.06, 'Cl': 35.45, 'K': 39.098, 'Ca': 40.078, 'Fe': 55.845,
        'Cu': 63.546, 'Zn': 65.38, 'Ag': 107.87, 'I': 126.90, 'Ba': 137.33,
        'Pb': 207.2, 'U': 238.03
    };
    
    let weight = 0;
    let match;
    const regex = /([A-Z][a-z]*)(\d*)/g;
    
    while ((match = regex.exec(formula)) !== null) {
        const element = match[1];
        const count = match[2] ? parseInt(match[2]) : 1;
        
        if (atomicWeights[element]) {
            weight += atomicWeights[element] * count;
        }
    }
    
    return weight > 0 ? weight : null;
}

// Export functions
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    rows.forEach(row => {
        const rowData = [];
        const cols = row.querySelectorAll('td, th');
        
        cols.forEach(col => {
            // Remove button HTML and get text content
            let text = col.textContent.trim();
            text = text.replace(/\n/g, ' ');
            text = text.replace(/\s+/g, ' ');
            rowData.push(`"${text}"`);
        });
        
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (navigator.msSaveBlob) {
        navigator.msSaveBlob(blob, filename);
    } else {
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Alert auto-dismiss
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);