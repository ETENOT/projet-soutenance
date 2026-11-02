{{-- Message affiché à une ENTREPRISE à la place des classes d'un cours.
     Ses classes sont programmées sur mesure par l'administrateur (menu « Mes sessions »). --}}
<div class="text-center py-5 px-3">
    <span class="avatar-lg d-inline-block mb-3">
        <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-1">
            <i class="ri-customer-service-2-line"></i>
        </span>
    </span>
    <h5 class="fw-bold mb-2">Classes sur mesure pour votre entreprise</h5>
    <p class="text-muted mb-0 mx-auto" style="max-width: 520px;">
        Pour inscrire des membres de votre entreprise à une classe de ce cours, contactez le
        <strong>service client de NéoVision</strong> afin de faire programmer des classes.
        Elles apparaîtront ensuite dans <a href="{{ route('entreprise.sessions.index') }}">Mes sessions</a>.
    </p>
</div>