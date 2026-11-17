import './bootstrap';

import Alpine from 'alpinejs';
import kiosk from './kiosk';
import productForm from './product-form';
import ordersMonitor from './orders-monitor';

Alpine.data('kiosk', kiosk);
Alpine.data('productForm', productForm);
Alpine.data('ordersMonitor', ordersMonitor);

window.Alpine = Alpine;

Alpine.start();
