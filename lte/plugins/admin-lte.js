import LteLayout from "../layout/LteLayout.vue";
import LteHeader from "../components/LteHeader.vue";
import LteFooter from "../components/LteFooter.vue";
import LteSidebar from "../components/LteSidebar.vue";
import LteContentHeader from "../components/LteContentHeader.vue";
import LteCard from "../components/LteCard.vue";
import LteModal from "../components/LteModal.vue";
import LteGrid from "../components/LteGrid.vue";
import LteWindow from "../components/LteWindow.vue";

export default {
    install(app, options){
        //注册组件
        app.component("lte-layout", LteLayout);
        app.component("lte-header", LteHeader);
        app.component("lte-footer", LteFooter);
        app.component("lte-sidebar", LteSidebar);
        app.component("lte-content-header", LteContentHeader);
        app.component("lte-card", LteCard);
        app.component("lte-modal", LteModal);
        app.component("lte-grid", LteGrid);
        app.component("lte-window", LteWindow);
    }
}
