<div>
    <!-- Create Customer Modal -->
    @if($showModal)
        <div class="modal modal-blur fade show" style="display: block;" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Customer</h5>
                        <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                        <input wire:model="customer_name" type="text" class="form-control @error('customer_name') is-invalid @enderror" placeholder="Enter customer name">
                                        @error('customer_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input wire:model="phone_number" type="text" class="form-control @error('phone_number') is-invalid @enderror" placeholder="Enter phone number">
                                        @error('phone_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="Enter email address">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Customer Group</label>
                                        <select wire:model="customer_group" class="form-select @error('customer_group') is-invalid @enderror">
                                            <option value="">Select group</option>
                                            <option value="VIP">VIP</option>
                                            <option value="Regular">Regular</option>
                                            <option value="New">New</option>
                                            <option value="Wholesale">Wholesale</option>
                                            <option value="Retail">Retail</option>
                                        </select>
                                        @error('customer_group')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea wire:model="address" class="form-control @error('address') is-invalid @enderror" rows="2" placeholder="Enter customer address"></textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Customer Type</label>
                                        <div class="form-selectgroup">
                                            <label class="form-selectgroup-item">
                                                <input wire:model="customer_type" type="radio" name="customer_type" value="individual" class="form-selectgroup-input">
                                                <span class="form-selectgroup-label">Individual</span>
                                            </label>
                                            <label class="form-selectgroup-item">
                                                <input wire:model="customer_type" type="radio" name="customer_type" value="company" class="form-selectgroup-input">
                                                <span class="form-selectgroup-label">Company</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Gender</label>
                                        <select wire:model="gender" class="form-select @error('gender') is-invalid @enderror">
                                            <option value="">Select gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Birthday</label>
                                        <input wire:model="birthday" type="date" class="form-control @error('birthday') is-invalid @enderror">
                                        @error('birthday')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" wire:click="closeModal" class="btn btn-outline-secondary">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Customer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
