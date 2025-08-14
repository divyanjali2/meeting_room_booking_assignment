import './bootstrap';
import Alpine from 'alpinejs';
import toastr from 'toastr';
import 'toastr/build/toastr.css';

window.Alpine = Alpine;
window.toastr = toastr;

// Configure toastr
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: 3000
};

Alpine.start();
