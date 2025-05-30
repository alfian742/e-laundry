<!-- Help center modal -->
<div class="modal fade" id="helpCenterModal" tabindex="-1" role="dialog" aria-labelledby="helpCenterModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body p-3">
                <div class="d-flex justify-content-between align-items-center" style="gap: .5rem">
                    <h5 class="modal-title mb-0" id="helpCenterModalLabel">Pusat Bantuan</h5>
                    <button type="button" class="px-2 py-1 close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <hr class="mt-3 mb-4">

                <div class="mb-4">
                    <p class="text-lead">Jika Anda membutuhkan bantuan atau memiliki pertanyaan, silakan hubungi admin
                        melalui WhatsApp: <span class="text-success">{{ $site->phone_number }}</span> atau klik
                        tombol di bawah ini.</p>

                    <div class="alert alert-light" role="alert">
                        <p class="text-lead mb-0">
                            <span class="text-danger">*</span> Layanan tersedia pada hari
                            {{ $site->operational_hours }}.
                        </p>
                    </div>
                </div>

                <a href="https://wa.me/{{ formatPhoneNumber($site->phone_number) }}?text=Hai Admin {{ $site->site_name }}."
                    target="_blank" class="btn btn-lg btn-block btn-icon icon-left btn-success">
                    <i class="fab fa-whatsapp"></i> Hubungi Admin
                </a>
            </div>
        </div>
    </div>
</div>
