import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export function useOrderActions() {
    const deleteModal = ref({
        show: false,
        orderId: null,
        orderNumber: ''
    });

    const showDeleteModal = (orderId, orderNumber) => {
        deleteModal.value = {
            show: true,
            orderId,
            orderNumber
        };
    };

    const hideDeleteModal = () => {
        deleteModal.value = {
            show: false,
            orderId: null,
            orderNumber: ''
        };
    };

    const confirmDelete = () => {
        const { orderId, orderNumber } = deleteModal.value;
        
        if (orderId) {
            router.delete(route('orders.destroy', orderId), {
                onSuccess: () => {
                    hideDeleteModal();
                },
                onError: (errors) => {
                    alert('No se pudo eliminar la orden. Inténtalo de nuevo.');
                    hideDeleteModal();
                }
            });
        }
    };

    return {
        deleteModal,
        showDeleteModal,
        hideDeleteModal,
        confirmDelete
    };
}
